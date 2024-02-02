<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;

class UserStudyRecalculationTest extends SlimStagingTestCase {
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    //public const CAUSE_VARIABLE_NAME = 'Daily Step Count';
    public const CAUSE_VARIABLE_NAME = "Optimized Folate By Life Extension";
    public function testUserStudyRecalculation(){
        $enabled = false;
        if(!$enabled){
            $this->skipTest("Too slow to generate user_variable_relationships over parameters because it's not an API request in test.");
            return;
        }
		$expectedString = '';
		$cause = QMUserVariable::findByNameIdSynonymOrSpending(230, self::CAUSE_VARIABLE_NAME);
		$min = $cause->minimumAllowedValueInCommonUnit;
        $this->checkMeasurements($cause, $min);
        $correlation = QMUserVariableRelationship::findByNamesOrIds(230, self::CAUSE_VARIABLE_NAME,
            "Overall Mood");
		$study = $correlation->findInMemoryOrNewQMStudy();
		//$study->publishToJekyll(false);
        $this->checkPairs($correlation, $min, $cause);
        $responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(134);
		$this->checkQueryCount(19);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v4/study',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '_ga=GA1.2.506304397.1541100803; _gid=GA1.2.1750451632.1541100803; __cfduid=d6fc81d9344afdf0201f6ec5be411fa501541101912; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fsrc%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26message%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1542383060%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; fbm_225078261031461=base_domain=.quantimo.do',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
      'HTTP_X_APP_VERSION' => '2.8.1101',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' =>
      [
        'causeVariableName' => self::CAUSE_VARIABLE_NAME,
        'effectVariableName' => 'Overall Mood',
        'clientId' => 'quantimodo',
        'includeCharts' => 'true',
        'platform' => 'web',
        'recalculate' => 'true',
      ],
      'responseStatusCode' => NULL,
      'unixtime' => 1541206895,
      'requestDuration' => 1.0503511428833008,
    ];
    /**
     * @param QMUserVariableRelationship|null $correlation
     * @param float $min
     * @param QMUserVariable $cause
     * @throws \App\Exceptions\NotEnoughMeasurementsForCorrelationException
     * @throws \App\Exceptions\TooManyMeasurementsException
     */
    private function checkPairs(?QMUserVariableRelationship $correlation, float $min, QMUserVariable $cause): void{
        $pairs = $correlation->getPairs();
        foreach($pairs as $pair){
            if($pair->causeMeasurementValue < $min){
                throw new \LogicException("$pair->causeMeasurementValue is less than min $min for $cause");
            }
        }
    }
    /**
     * @param QMUserVariable $cause
     * @param float $min
     */
    private function checkMeasurements(QMUserVariable $cause, float $min): void{
        $measurements = $cause->getValidDailyMeasurementsWithTagsAndFilling();
        foreach($measurements as $measurement){
            if($measurement->getValue() < $min){
                throw new \LogicException("$measurement->value is less than min $min for $cause");
            }
        }
    }
}
