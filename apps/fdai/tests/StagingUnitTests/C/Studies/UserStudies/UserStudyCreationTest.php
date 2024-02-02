<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use Tests\SlimStagingTestCase;

class UserStudyCreationTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testStudyCreateSleepStartMood(): void{
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
        $this->checkTestDuration(26, 
                                 "This should be fast because we should only generate charts in GetStudyController");
		$this->checkQueryCount(20);
	}
	public $expectedResponseSizes = [
        'success'     => 0.004,
        'status'      => 0.009,
        'description' => 0.255,
        'summary'     => 0.022,
        'study'       => 259, //
    ];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/study/create',
        'SERVER_NAME' => \App\Computers\ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT' => '443',
        'HTTP_X_FIRELOGGER' => '1.3',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://dev-web.quantimo.do/index.html',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
        'HTTP_X_APP_VERSION' => '2.8.1230',
        'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
        'HTTP_X_TIMEZONE' => 'America/Chicago',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '96',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '{"causeVariableName":"Sleep Start Time","effectVariableName":"Overall Mood","type":"individual"}',
        'slim.request.form_hash' => [],
        'slim.request.query_hash' =>
          [
            'clientId' => 'quantimodo',
            'platform' => 'web',
          ],
        'responseStatusCode' => 201,
        'unixtime' => 1547775617,
        'requestDuration' => 44.52920889854431,
    ];
}
