<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\Feed;
use App\Computers\ThisComputer;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;
class FeedPostTest extends SlimStagingTestCase {
    public function testFeedPost(){
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->assertCountGreaterThan(1, $responseBody->cards);
		$this->checkTestDuration(27);
		$this->checkQueryCount(13);
	}
	public $expectedResponseSizes = [
      //'error' => 1.0,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'POST',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/feed',
      'QUERY_STRING' => 'clientId=quantimodo&platform=web',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => '__stripe_mid=6f430ca4-9d0b-4469-9f73-cdb54da696e6; PHPSESSID=cache-sync-status; bp-members-scope=all; XDEBUG_SESSION=PHPSTORM; __cfduid=d400bc2cadb7a79f4a01b284bef2ac92a1538419547; _ga=GA1.2.374266088.1538419637; fbm_225078261031461=base_domain=.quantimo.do; heateorSsSLOptin=1; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fsrc%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26clientSecret%3DTcQArZOoUecO9O4aBvntUl6v1QzzsU38%26message%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1540423726%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; _gid=GA1.2.724478634.1539362406',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
      'HTTP_X_APP_VERSION' => '2.8.1001',
      'HTTP_ORIGIN' => 'https://local.quantimo.do',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '2',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '[]',
      'slim.request.form_hash' =>
      [
      ],
      'slim.request.query_hash' =>
      [
        'clientId' => 'quantimodo',
        'platform' => 'web',
      ],
      'responseStatusCode' => 201,
      'unixtime' => 1539393090,
      'requestDuration' => 2.507750988006592,
    ];
}
