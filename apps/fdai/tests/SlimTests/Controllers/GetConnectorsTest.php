<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpDeprecationInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\SlimTests\Controllers;
use App\Buttons\QMButton;
use App\DataSources\QMClient;
use App\DataSources\QMConnector;
use App\DataSources\QMDataSource;
use App\Logging\QMLog;
use App\Logging\QMLogLevel;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Base\BasePlatformProperty;
use App\Types\QMStr;
use App\Utils\AppMode;
class GetConnectorsTest extends \Tests\SlimTests\SlimTestCase {
	public function setUp(): void{
		if(AppMode::isWindows()){
			//le("Skipping GetConnectorsTest because it doesn't work on Windows");
		}
		parent::setUp(); 
	}
	protected static function compareConnectorsResponse(string $key, $obj, string $message = null): void{
		foreach($obj as $i => $one){
			/** @var QMConnector $one */
			unset($one->connectInstructions->url);  // The state param changes for some reason
			$obj[$i] = $one;
		}
		parent::compareObjectFixture($key, $obj, $message);
	}
	/**
	 * @param QMConnector|object $c
	 */
    private function checkConnector($c){
	    $this->assertNotNull($c->buttons, $c->name);
        $this->assertGreaterThan(0, count($c->buttons));
        /** @var QMButton $button */
        foreach ($c->buttons as $button){
            if(str_contains($button->id, '-state-button')){continue;}
            $text = QMStr::slugify($button->text);
			if(!$button->id){
				le("button id is null", $button);
			}
            $this->assertStringContainsStringIgnoringCase($text, $button->id);
            $this->assertNotContains('button-button', $button->id);
        }
		$printed = QMLog::print_r($c, true);
        $commonConnector = QMDataSource::find($c->name);
        $this->assertEquals($commonConnector->id, $c->id, $c->name." common".$commonConnector->name);
        $this->assertNotEmpty($c->buttons);
        $this->assertObjectHasAttribute('id', $c, 
                                        "assertObjectHasAttribute('id') failed for: " . $printed);
        $this->assertIsInt($c->id, "assertInternalType int for id failed for: " . $printed);
        $this->assertObjectHasAttribute('name', $c, 
                                        "assertObjectHasAttribute('name') failed for: " . $printed);
        $this->assertIsString($c->name, "assertInternalType string for name failed for: " . $printed);
        $this->assertObjectHasAttribute('displayName', $c, 
                                        "assertObjectHasAttribute('displayName') failed for: " . $printed);
        $this->assertIsString($c->displayName, "assertInternalType string for displayName failed for: " . $printed);
        $this->assertObjectHasAttribute('image', $c, 
                                        "assertObjectHasAttribute('image') failed for: " . $printed);
        $this->assertIsString($c->image, "assertInternalType string for image failed for: " . $printed);
        if($c->affiliate){
            $this->assertObjectHasAttribute('getItUrl', $c, 
                                            "assertObjectHasAttribute('getItUrl') failed for: " . $printed);
            $this->assertIsString($c->getItUrl, 
                                  "assertInternalType string for getItUrl failed for: " . $printed);
        }
        $this->assertPropertyExistsAndOutputIfFalse($c, 'qmClient');
        if(!$c->qmClient){
            $this->assertObjectHasAttribute('connected', $c, 
                                            "assertObjectHasAttribute('connected') failed for: " . $printed);
            $this->assertIsBool($c->connected, "connected is not bool failed for: " . $printed);
			if(!$c->spreadsheetUpload){
				$this->assertObjectHasAttribute('connectInstructions', $c,
					"assertObjectHasAttribute('connectInstructions') failed for: " . $printed);
			}
            //$this->assertIsArray( $connector->connectInstructions, "assertInternalType array for connectInstructions failed for: " . $printed);
            $this->assertObjectHasAttribute('lastSuccessfulUpdatedAt', $c, "assertObjectHasAttribute('lastSuccessfulUpdatedAt') failed for: " . $printed);
            //$this->assertIsInt($item->lastSuccessfulUpdatedAt, "assertInternalType int for lastUpdate failed for: " . $printed);
            //$this->assertObjectHasAttribute('totalMeasurementsInLastUpdate', $c, "assertObjectHasAttribute
	        //('totalMeasurementsInLastUpdate') failed for: " . $printed);
            //$this->assertIsInt($item->totalMeasurementsInLastUpdate, "assertInternalType int for totalMeasurementsInLastUpdate failed for: " . $printed);
        }
        if(isset($c->connectError)){
            QMLog::infoWithoutContext("updateError for $c->name: ".$c->connectError);
        }
        if(isset($c->updateError)){
            QMLog::infoWithoutContext("updateError for $c->name: ".$c->updateError);
        }
        if(isset($c->connectError) || isset($c->updateError)){
            $this->assertNotNull($c->errorMessage, QMLog::print_r($c, true));
        }
    }
    /**
     * Test /api/connectors/list method to get list of connectors
     * @group api
     */
    public function testGetConnectorsListV1(){
        $connectors = $this->getConnectorsResponseForAuthenticatedUser(1);
        foreach ($connectors as $c) {
	        $this->assertNotNull($c->userId, $c->name);
			$this->checkConnector($c);
			if(!$c->spreadsheetUpload){
				$this->assertFalse($c->connected);
				$this->assertEquals('DISCONNECTED', $c->connectStatus, "connectStatus for $c->name");
			}
		}
		$this->assertCount(26, $connectors);
        //$this->compareConnectorsResponse(__FUNCTION__, $connectors, 'connectors');
	    // Can't get consistent results from this test because the state param changes for some reason
        $this->assertQueryCountLessThan(19, false);
    }
    public function testGetConnectorsListV3(){
	    $original = QMLogLevel::upper();
        //QMLog::setLogLevelToDebug();
        $connectors = $this->getConnectorsResponseForAuthenticatedUser(3);
        $medHelperPresent = false;
        foreach ($connectors as $c) {
            if(isset($c->spreadsheetUpload)){
                if($c->name === "medhelper"){$medHelperPresent = true;}
                continue;
            }
	        $this->assertNotNull($c->userId, $c->name);
            $this->checkConnector($c);
        }
        $this->assertTrue($medHelperPresent, "Medhelper not returned!");
	    $this->assertCount(26, $connectors);
	    //$this->compareConnectorsResponse(__FUNCTION__, $connectors, 'connectors');
	    // Can't get consistent results from this test because the state param changes for some reason
	    $this->assertQueryCountLessThan(6);
	    QMLogLevel::set($original);
    }
    public function testConnectorsLogin(){
        $this->setAuthenticatedUser(null);
        $response = $this->getAndDecodeBody('/api/v3/connectors/list', []);
        $connectors = $response->connectors;
        $this->assertTrue(count($connectors) > 5, "We only got ".count($connectors)." login connectors");
        $medHelperPresent = false;
        /** @var QMConnector[] $connectors */
        foreach ($connectors as $connector) {
            $this->assertNotTrue(isset($connector->userId));
            $this->assertNotTrue($connector->spreadsheetUpload, "Got spreadsheet upload connector when not logged in!");
            if($connector->name === "medhelper"){$medHelperPresent = true;}
            $this->checkConnector($connector);
        }
        $this->compareConnectorsResponse(__FUNCTION__, $connectors, 'connectors');
        $this->assertFalse($medHelperPresent);
        $this->assertQueryCountLessThan(2);
    }
    public function testGetConnectorsForMobile(){
        $this->verifyClientIdAndSecret(BasePlatformProperty::PLATFORM_ANDROID);
        $this->verifyClientIdAndSecret(BasePlatformProperty::PLATFORM_IOS);
        $this->verifyClientIdAndSecret(BasePlatformProperty::PLATFORM_WEB);
        $this->verifyClientIdAndSecret(null);
    }
    /**
     * @param $platform
     */
    private function verifyClientIdAndSecret($platform){
        $connectors = $this->getConnectorsResponseForAuthenticatedUser(1,
            ['platform' => $platform, 'client_id' => BaseClientIdProperty::CLIENT_ID_OAUTH_TEST_CLIENT,
             'client_secret' => QMClient::TEST_CLIENT_SECRET]);
        foreach ($connectors as $c){
            $this->assertFalse(isset($c->connectorClientSecret),
                "We should not be returning client secret but got one for $c->name");
        }
    }
    /**
     * @param int $version
     * @param array $params
     * @return QMConnector[]
     */
    private function getConnectorsResponseForAuthenticatedUser(int $version, array $params = []): array {
        $this->setAuthenticatedUser(1);
        $bodyDecoded = $this->getAndDecodeBody('/api/v' . $version . '/connectors/list', $params);
        if($version > 1){
            $connectors = $bodyDecoded->connectors;
        } else {
            $connectors = $bodyDecoded;
        }
        $this->assertIsArray($connectors);
        return $connectors;
    }
}
