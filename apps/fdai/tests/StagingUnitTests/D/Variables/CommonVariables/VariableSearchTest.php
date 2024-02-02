<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Logging\QMLog;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\Variable\GetUserVariableRequest;
use App\Slim\View\Request\Variable\GetVariableRequest;
use App\Storage\Memory;
use App\Types\QMArr;
use App\Types\TimeHelper;
use App\Variables\CommonVariables\EnvironmentCommonVariables\PollenIndexCommonVariable;
use App\Variables\CommonVariables\NutrientsCommonVariables\ProteinCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use Tests\SlimStagingTestCase;

class VariableSearchTest extends SlimStagingTestCase
{
    public function testGetPollenIndex(){
        $v = QMUserVariable::findOrCreateByNameOrId(82951, PollenIndexCommonVariable::NAME);
        $this->assertEquals(PollenIndexCommonVariable::NAME, $v->name);
    }
    public function testVariableSearch(){
        $demo = QMUser::demo();
        $this->checkGetUserVariablesSimple($demo);
        $this->checkGetVariablesSimple($demo);
        $this->checkApiRequest();
        $this->checkTestDuration(12);
		$this->checkQueryCount(16);
	}
	public $expectedResponseSizes = [
      0 => 15.0,
      1 => 15.0,
      2 => 15,
      3 => 15,
    ];
	public /** @noinspection SpellCheckingInspection */
        $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/variables',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_COOKIE' => '__cfduid=de78cdbf1ead141f967d1ccd011dc619d1541007864; _ga=GA1.2.37646767.1541007870; _gid=GA1.2.2057189300.1541007870; _gat=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/www/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer demo',
      'HTTP_X_APP_VERSION' => '2.8.1031',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' =>
      [
        'limit' => '100',
        'sort' => '-latestTaggedMeasurementTime',
        'includePublic' => 'false',
        'clientId' => 'quantimodo',
        'searchPhrase' => 'Protein',
        'platform' => 'web',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1541008275,
      'requestDuration' => 15.515078067779541,
    ];
    /**
     * @param QMVariable[] $variables
     */
    private function makeSureVariablesAreChronological(array $variables): void{
        $previous = now_at();
        $previousVariable = null;
        foreach ($variables as $variable) {
			if(!property_exists($variable, 'latestTaggedMeasurementStartAt')){
				le("Variable {$variable->name} has no latestTaggedMeasurementStartAt");
			}
            $current = $variable->latestTaggedMeasurementStartAt;
            QMLog::info("$variable->name " . TimeHelper::timeSinceHumanString($current));
            if ($variable->name === ProteinCommonVariable::NAME) {continue;}
            if(!$current){continue;}
            self::assertDateLessThanOrEqual($previous, $current,
                '$previousLatestMeasurement', 'latestTaggedMeasurementTime');
            $previous = $current;
        }
    }
    /**
     * @param $demo
     * @return array
     */
    private function checkGetUserVariablesSimple(QMUser $demo): array{
        $variables = GetUserVariableRequest::getVariablesSimple(ProteinCommonVariable::NAME, $demo);
        /** @var QMUserVariable[] $byName */
        $byName = QMArr::indexBy($variables, 'name');
        foreach($byName as $v){
            $latestMeasurementTimes[$v->name] = $v->latestMeasurementTime;
            $latestNonTaggedMeasurementStartAts[$v->name] = $v->latestNonTaggedMeasurementStartAt;
	        $latestTaggedMeasurementStartAts[$v->name] = $v->latestTaggedMeasurementStartAt;
        }
        $this->assertEquals(ProteinCommonVariable::NAME, $variables[0]->name);
        $this->assertGreaterThan(0, $variables[0]->numberOfMeasurements);
        $variables = QMArr::sortByProperty($variables, '-latestMeasurementTime');
        $this->makeSureVariablesAreChronological($variables);
        return $variables;
    }
    /**
     * @param $demo
     * @return array
     */
    private function checkGetVariablesSimple(QMUser $demo): array{
        Memory::resetClearOrDeleteAll();
        $variables = GetVariableRequest::getVariablesSimple(ProteinCommonVariable::NAME, $demo);
        $this->assertEquals(ProteinCommonVariable::NAME, $variables[0]->name);
        $this->assertGreaterThan(0, $variables[0]->numberOfMeasurements);
        $protein = QMUserVariable::getByNameOrId($demo->getId(), ProteinCommonVariable::NAME);
        $this->assertEquals(ProteinCommonVariable::NAME, $protein->name);
        $this->assertGreaterThan(0, $protein->numberOfMeasurements);
        return $variables;
    }
    private function checkApiRequest(): void{
        Memory::resetClearOrDeleteAll();
        $expectedString = ProteinCommonVariable::NAME;
        /** @var QMVariable[] $variables */
        $variables = $this->callAndCheckResponse($expectedString);
        $this->assertEquals(ProteinCommonVariable::NAME, $variables[0]->name);
        $this->makeSureVariablesAreChronological($variables);
    }
}
