<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\OAuth;
use App\Exceptions\AccessTokenExpiredException;
use Tests\QMBaseTestCase;
use Tests\SlimStagingTestCase;
class ExpiredTokenInBothQueryAndHeaderTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testExpiredTokenInBothQueryAndHeader(): void{
        QMBaseTestCase::setExpectedRequestException(AccessTokenExpiredException::class);
		$responseBody = $this->callAndCheckResponse('The access token provided has expired');
		$this->checkTestDuration(16);
		$this->checkQueryCount(1);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '67.188.149.122',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v1/trackingReminderNotifications/past',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_COOKIE' => '_ga=GA1.2.1444758548.1518041917; __cfduid=d478d58b8e11710f96278c5db208766e21551657128',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_SEC_FETCH_SITE' => 'cross-site',
        'HTTP_ACCEPT' => '*/*',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'chromeExtension',
        'HTTP_AUTHORIZATION' => 'Bearer expired-test-token',
        'HTTP_X_CLIENT_ID' => 'moodimodoapp',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36',
        'HTTP_X_APP_VERSION' => '2.9.809',
        'HTTP_DNT' => '1',
        'HTTP_SEC_FETCH_MODE' => 'cors',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => '',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
  [
    'access_token' => '840180d76cd543a4837339aabeef907b0c3d994a',
    'appName' => 'MoodiModo',
    'appVersion' => '2.9.809',
    'clientId' => 'moodimodoapp',
    'platform' => 'chromeExtension',
  ],
        'responseStatusCode' => 401,
        'unixtime' => 1565532115,
        'requestDuration' => 0.6891429424285889,
    ];
}
