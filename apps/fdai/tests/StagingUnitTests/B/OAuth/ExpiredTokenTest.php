<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\OAuth;
use App\Exceptions\AccessTokenExpiredException;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;
class ExpiredTokenTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public $expectedCode = 401;
    public function testExpiredToken(): void{
		QMBaseTestCase::setExpectedRequestException(AccessTokenExpiredException::class);
		$responseBody = $this->callAndCheckResponse('The access token provided has expired');
		$this->checkTestDuration(17);
		$this->checkQueryCount(1);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.190.186.194',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/trackingReminderNotifications',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_CDN_LOOP' => 'cloudflare',
        'HTTP_CF_CONNECTING_IP' => '96.35.14.161',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_REFERER' => 'https://medimodo.quantimo.do/',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'medimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer expired-test-token',
        'HTTP_X_APP_VERSION' => '2.9.403',
        'HTTP_ORIGIN' => 'https://medimodo.quantimo.do',
        'HTTP_X_TIMEZONE' => 'America/Chicago',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CF_VISITOR' => '{"scheme":"https"}',
        'HTTP_X_FORWARDED_PROTO' => 'https',
        'HTTP_CF_RAY' => '4c1e9ff549d5c50e-ORD',
        'HTTP_X_FORWARDED_FOR' => '108.162.216.196',
        'HTTP_CF_IPCOUNTRY' => 'US',
        'HTTP_ACCEPT_ENCODING' => 'gzip',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
  [
    'sort' => '-reminderTime',
    'limit' => '100',
    'reminderTime' => '(lt)2019-04-03 22:59:27',
    'clientId' => 'medimodo',
  ],
        'responseStatusCode' => NULL,
        'unixtime' => 1554332073,
        'requestDuration' => 1.004025936126709,
    ];
}
