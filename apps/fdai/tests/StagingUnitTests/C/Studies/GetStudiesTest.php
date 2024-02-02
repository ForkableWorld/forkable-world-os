<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class GetStudiesTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetStudies(): void{
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(9);
		$this->checkQueryCount(19);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '83.68.6.111',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/studies',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_ACCEPT_LANGUAGE' => 'nl-NL,nl;q=0.9,en-US;q=0.8,en;q=0.7',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://web.quantimo.do/',
        'HTTP_SEC_FETCH_MODE' => 'cors',
        'HTTP_SEC_FETCH_SITE' => 'same-site',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 9; SM-A405FN) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Mobile Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_X_APP_VERSION' => '2.9.1128',
        'HTTP_ORIGIN' => 'https://web.quantimo.do',
        'HTTP_X_TIMEZONE' => 'Europe/Amsterdam',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
  [
    'clientId' => 'quantimodo',
    'platform' => 'web',
    'limit' => '10',
  ],
        'responseStatusCode' => NULL,
        'unixtime' => 1575535174,
        'requestDuration' => 0.7717440128326416,
    ];
}
