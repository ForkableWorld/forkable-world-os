<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\AppSettings;
use App\Properties\Base\BaseClientIdProperty;
use Tests\SlimStagingTestCase;

class DemoClientTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testDemoClient(): void{
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(18);
		$this->checkQueryCount(6);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '24.216.168.142',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v3/connectors/list',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://demo.quantimo.do/',
      'HTTP_SEC_FETCH_SITE' => 'same-site',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.75 Safari/537.36',
      'HTTP_X_CLIENT_ID' => BaseClientIdProperty::CLIENT_ID_DEMO,
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer demo',
      'HTTP_ORIGIN' => 'https://demo.quantimo.do',
      'HTTP_X_TIMEZONE' => 'America/Chicago',
      'HTTP_SEC_FETCH_MODE' => 'cors',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' =>
      [
        'clientId' => BaseClientIdProperty::CLIENT_ID_DEMO,
        'platform' => 'web',
        'limit' => '10',
      ],
      'responseStatusCode' => NULL,
      'unixtime' => 1568912792,
      'requestDuration' => 1.8017399311065674,
    ];
}
