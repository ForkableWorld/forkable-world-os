<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables;
use App\Logging\QMLog;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;

class SearchForVariableWithCategoryFilterTest extends SlimStagingTestCase{
    public $maximumResponseArrayLength = 4;
    public $minimumResponseArrayLength = 1;
    public function testSearchForVariableWithCategoryFilter(){
		$expectedString = 'Creatine Monohydrate Powder';
        /** @var QMUserVariable[] $responseBody */
        $responseBody = $this->callAndCheckResponse($expectedString);
        foreach ($responseBody as $variable){QMLog::info($variable->name);}
		$this->assertTrue(count($responseBody) >= $this->minimumResponseArrayLength,
            "Less than $this->minimumResponseArrayLength results!");
        $this->assertTrue(count($responseBody) <= $this->maximumResponseArrayLength,
            "More than $this->maximumResponseArrayLength results!");
		$this->checkTestDuration(18);
		$this->checkQueryCount(4);
	}
	public $expectedResponseSizes = [
      0 => 125.232,
      1 => 10.0,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/variables',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; _ga=GA1.2.956197214.1538009354; __cfduid=d1d1a0e2822985ef9d386e30f478657f01538012107; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fsrc%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26clientSecret%3DTcQArZOoUecO9O4aBvntUl6v1QzzsU38%26message%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1539234481%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; _gid=GA1.2.1384267982.1538253686',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      //'HTTP_AUTHORIZATION' => 'Bearer '.QMAccessToken::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
      'HTTP_X_APP_VERSION' => '2.8.930',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' => [
        'limit' => '50',
        'sort' => '-latestTaggedMeasurementTime',
        'includePublic' => 'true',
        'clientId' => 'quantimodo',
        'searchPhrase' => 'Creatine Pow',
        'platform' => 'web',
        'variableCategoryName' => 'Treatments',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1538361067,
      'requestDuration' => 9.719656944274902,
    ];
}
