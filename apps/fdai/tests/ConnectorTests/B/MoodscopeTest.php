<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\B;
use App\DataSources\Connectors\MoodscopeConnector;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use Tests\ConnectorTests\ConnectorTestCase;
class MoodscopeTest extends ConnectorTestCase {
    protected const DISABLED_UNTIL = "2018-08-02";
    public $connectorName = MoodscopeConnector::NAME;
    public $variablesToCheck = [OverallMoodCommonVariable::NAME];
    public function testMoodscope(){
		$this->skipTest("Doesn't work with new version");
		$this->credentials = [
			'username' => MoodscopeConnector::TEST_USERNAME,
			'password' => MoodscopeConnector::TEST_PASSWORD,
		];
		$this->fromTime = strtotime("2012-01-01");
        $this->connectImportDisconnect();
    }
}
