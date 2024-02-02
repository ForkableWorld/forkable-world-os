<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\D\Variables\UserVariables;
use Tests\SlimStagingTestCase;

class PostUserVariablesAsAdminTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostUserVariablesAsAdmin(): void{
		$expectedString = '';
		$responseBody = $this->callAndCheckResponse($expectedString);
		$this->checkTestDuration(16);
		$this->checkQueryCount(6);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'POST',
  'REMOTE_ADDR' => '10.0.2.2',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/userVariables',
  'SERVER_NAME' => \App\Computers\ThisComputer::LOCAL_HOST_NAME,
  'SERVER_PORT' => '443',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
  'HTTP_CONTENT_TYPE' => 'application/json',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.86 Safari/537.36',
  'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
  'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
  'HTTP_ACCEPT' => 'application/json',
  'HTTP_CACHE_CONTROL' => 'no-cache',
  'HTTP_PRAGMA' => 'no-cache',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '269',
  'CONTENT_TYPE' => 'application/json',
  'slim.url_scheme' => 'https',
  'slim.input' => '{"variableId":1449,"durationOfAction":604800,"fillingValue":null,"maximumAllowedValue":null,"onsetDelay":0,"combinationOperation":"SUM","shareUserMeasurements":true,"userVariableVariableCategoryName":"Nutrients","experimentStartTimeString":null}',
  'slim.request.form_hash' =>
  array (
  ),
  'slim.request.query_hash' =>
  array (
    'appName' => 'QuantiModo',
    'appVersion' => '2.9.403',
    'clientId' => 'quantimodo',
  ),
  'responseStatusCode' => NULL,
  'unixtime' => 1554483090,
  'requestDuration' => 11.52200698852539,
);
}
