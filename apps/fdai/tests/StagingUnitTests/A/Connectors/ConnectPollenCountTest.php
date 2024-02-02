<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A\Connectors;
use App\Computers\ThisComputer;
use App\Models\Connection;
use Tests\SlimStagingTestCase;
class ConnectPollenCountTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testConnectPollenCount(): void{
        $before = Connection::whereWaiting()->count();
        //QMConnector::updateDatabaseTableFromHardCodedConstants();
		$responseBody = $this->callAndCheckResponse('Connected');
		$after = Connection::whereWaiting()->count();
		$this->checkTestDuration(23);
		$this->checkQueryCount(18);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '192.168.10.1',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/connectors/pollen-count/connect',
        'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT' => '443',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
        'HTTP_SEC_FETCH_MODE' => 'cors',
        'HTTP_SEC_FETCH_SITE' => 'same-site',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
        'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
        'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
        'HTTP_ACCEPT' => 'application/json, text/plain, */*',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => '',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
  [
    'appName' => 'QuantiModo',
    'appVersion' => '2.9.1128',
    'accessToken' => 'mike-test-token',
    'clientId' => 'quantimodo',
    'platform' => 'web',
    'XDEBUG_SESSION_START' => 'PHPSTORM',
    'zip' => '62025',
  ],
        'slim.request.form_hash' =>
  [],
        'responseStatusCode' => 200,
        'unixtime' => 1575773338,
        'requestDuration' => 10,
    ];
}
