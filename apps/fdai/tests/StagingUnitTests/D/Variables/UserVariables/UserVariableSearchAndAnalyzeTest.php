<?php /** @noinspection PhpUnhandledExceptionInspection */ /** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;

class UserVariableSearchAndAnalyzeTest extends SlimStagingTestCase {
    public function testVariableSearchAndAnalyze(){
		$expectedString = 'Cosentyx';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$userVariable = QMUserVariable::getByNameOrId(230, "Cosentyx");
		$userVariable->analyzeFully("we're testing", true);
		$this->assertEquals(0, $userVariable->numberOfTrackingReminders);
        $this->assertEquals(0.0, $userVariable->lastValue);
        $this->assertEquals(300, $userVariable->secondToLastValue);
    }
    public $expectedResponseSizes = [
        //0 => 53,
        //1 => 15
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '192.168.80.1',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/variables',
      'QUERY_STRING' => 'limit=50&sort=-latestTaggedMeasurementTime&includePublic=true&clientId=quantimodo&searchPhrase=cosen&platform=web',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; _ga=GA1.2.1935002470.1536952545; __cfduid=dc71f0972421f063f2be8e8665fdd8f471536953713; laravel_session=eyJpdiI6Ik45TkRDUWJBdjFia1dONVp0XC9UYjRnPT0iLCJ2YWx1ZSI6IlpXTExRdVVSYkNpOGJhUkdhNG1vcEt4THZTRndmVFlvcldKSDhFa25TSFZjdW5maFRlQmdlS2Yrb2szNjVuekhkRnBVdEJHMVRlZSswOTY5dUtmc2JBPT0iLCJtYWMiOiI5NDkxMDQ2MDY4ZmViODAzZjJiNjBhMGQ3MGMxOWRhYWZjZDRkNWIyN2U3MTMzYWQ4OWE5ZmNiN2ZiMDM3NzMwIn0%3D; _gid=GA1.2.436967688.1537406593; final_callback_url=https%3A%2F%2Fqm-staging.quantimo.do%2Fionic%2FModo%2Fwww%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Fqm-staging.quantimo.do%252Fionic%252FModo%252Fwww%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26message%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1538781777%7C2a66dd6a5567e50c8f6303c3bf2aa829%7Cquantimodo',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
      'HTTP_X_APP_VERSION' => '2.8.916',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' =>
      [
        'limit' => '50',
        'sort' => '-latestTaggedMeasurementTime',
        'includePublic' => 'true',
        'clientId' => 'quantimodo',
        'searchPhrase' => 'cosen',
        'platform' => 'web',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1537575323,
    ];
}
