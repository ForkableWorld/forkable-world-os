<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use Tests\SlimStagingTestCase;

class GetUserStudyFromStudiesEndpointByIdTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetUserStudyFromStudiesEndpointById(): void{
		$expectedString = '';
		$this->slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'GET',
  'REMOTE_ADDR' => '24.216.168.142',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/studies',
  'SERVER_NAME' => 'app.quantimo.do',
  'SERVER_PORT' => '443',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_REFERER' => 'https://web.quantimo.do/',
  'HTTP_SEC_FETCH_MODE' => 'cors',
  'HTTP_SEC_FETCH_SITE' => 'same-site',
  'HTTP_ORIGIN' => 'https://web.quantimo.do',
  'HTTP_X_FRAMEWORK' => 'ionic',
  'HTTP_X_PLATFORM' => 'web',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36',
  'HTTP_X_CLIENT_ID' => 'quantimodo',
  'HTTP_ACCEPT' => 'application/json',
  'HTTP_CONTENT_TYPE' => 'application/json',
  'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
  'HTTP_X_APP_VERSION' => '2.10.213',
  'HTTP_X_TIMEZONE' => 'America/Chicago',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => 'application/json',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  array (
    'causeVariableName' => 'Bananas (grams)',
    'effectVariableName' => 'Overall Mood',
    'userId' => '230',
    'clientId' => 'quantimodo',
    'includeCharts' => 'true',
    'platform' => 'web',
    'studyId' => 'cause-1981-effect-1398-user-230-user-study',
  ),
  'responseStatusCode' => NULL,
  'unixtime' => 1581634939,
  'requestDuration' => 0.4306471347808838,
);
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(10);
		$this->checkQueryCount(28);
	}
	public $expectedResponseSizes = [];
}
