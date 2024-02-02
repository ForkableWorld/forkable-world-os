<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Variables\QMVariable;
use Tests\SlimStagingTestCase;

class VariableWithCorrelationsTest extends SlimStagingTestCase
{
    public function testVariableWithCorrelations(){
		$expectedString = '';
        /** @var QMVariable[] $variables */
        $variables = $this->callAndCheckResponse($expectedString);
        $this->assertGreaterThan(0, count($variables));
		foreach ($variables as $variable){
		    $this->assertTrue(stripos($variable->subtitle, "studies") !== false,
                "subtitle ($variable->subtitle) should contain studies");
        }
		$this->checkTestDuration(12);
		$this->checkQueryCount(7);
	}
	public $expectedResponseSizes = [
      //0 => 29.0,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/variables',
      'QUERY_STRING' => 'variableCategoryName=Treatments&limit=50&sort=-numberOfCorrelationsAsCause&includePublic=true&clientId=quantimodo&searchPhrase=aspir&platform=web',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; _ga=GA1.2.956197214.1538009354; __cfduid=d1d1a0e2822985ef9d386e30f478657f01538012107; __utma=109117957.956197214.1538009354.1538493640.1538493640.1; __utmc=109117957; __utmz=109117957.1538493640.1.1.utmcsr=(direct)|utmccn=(direct)|utmcmd=(none); fbm_225078261031461=base_domain=.quantimo.do; heateorSsSLOptin=1; PHPSESSID=cache-sync-status; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1540431867%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; final_callback_url=https%3A%2F%2Fquantimo.do%2F; _gid=GA1.2.1950812831.1539611945; tk_tc=eEb38BXzU5nQHrjS; fbsr_225078261031461=AfQ7Gi2Oqn6Dq7ZOn9RG22rBVITugPiChTh452AMnnU.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUM0U01ra0M1dXJ1YnVpY2FTNjkwaEI4ZFNIWE0wM0hhd0N3bTRjMWVuZHBTRDhqYjdCSVE5ZTkxQTVxMHlCN2dtUHVfZXJnNEMwNXZPVHpTdFVOVDMyVDJJdENDRktPa0hoYTVXQ3U1QjJGUlFvOUpmZHlhYjNYdmE0dDA5YWdaNzBRWnd5X2lOUWZWR0lvYnNVMnQ4RkNFUzE2TzRBR2ZxTmlVWUNXUzJfZFNvYXVfYmRjUHZ0Q01oSGFiTW9wSEh6ZWdrQWhnYjZJS3h4RWRTeFhSUDluY01OQUQ1NWw0bjBPNW9hVXRwNnkzUGlDVFV1NHN6c3NCdURIa1Jkd28tUWJHYzJNQlgyRHk5bjlRczRhemNwQnpoS0JDc0t0WExCb2FEZVZCWmFWaTV4clVMTi1uNFV3NW1NQ2RNaVVnZkNyTGpyaXdoMVJzTU9RSFVhX0N2NSIsImlzc3VlZF9hdCI6MTUzOTY0NjQ5NywidXNlcl9pZCI6Ijc3ODM5Mjc2OCJ9; _gat=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
      'HTTP_X_APP_VERSION' => '2.8.930',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' =>
      [
        'variableCategoryName' => 'Treatments',
        'limit' => '50',
        'sort' => '-numberOfCorrelationsAsCause',
        'numberOfCorrelationsAsCause' => '(gt)0',
        'includePublic' => 'true',
        'clientId' => 'quantimodo',
        'searchPhrase' => 'aspir',
        'platform' => 'web',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1539647655,
      'requestDuration' => 6.582808971405029,
    ];
}
