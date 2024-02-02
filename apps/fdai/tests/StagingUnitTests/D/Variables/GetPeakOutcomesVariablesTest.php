<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Variables;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class GetPeakOutcomesVariablesTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetPeakOutcomesVariables(): void{
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->assertStringStartsWith("Peak Heart Rate Zone", $responseBody[0]->name);
		$this->checkTestDuration(16);
		$this->checkQueryCount(51);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '97.91.133.32',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/variables',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://web.quantimo.do/',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.131 Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
        'HTTP_X_APP_VERSION' => '2.9.412',
        'HTTP_ORIGIN' => 'https://web.quantimo.do',
        'HTTP_X_TIMEZONE' => 'America/Chicago',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
          [
            'limit' => '50',
            'sort' => '-numberOfCorrelationsAsCause',
            'includePublic' => 'true',
            'clientId' => 'quantimodo',
            'searchPhrase' => 'peak',
            'platform' => 'web',
          ],
        'responseStatusCode' => NULL,
        'unixtime' => 1557778788,
        'requestDuration' => 2.240920066833496,
    ];
}
