<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\Models\Variable;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Studies\QMStudy;
use Tests\SlimStagingTestCase;
use Tests\Traits\TestsStudies;

class PostCreateUserStudyWithTaggedVariablesTest extends SlimStagingTestCase
{
	use TestsStudies;
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostCreateUserStudyWithTaggedVariables(): void{
		$causeVariableName = "Folic Acid";
		$cause = Variable::findByNameOrId($causeVariableName);
		$instructions = $cause->getTrackingInstructionsHtml();
		$this->compareHtmlFragment("instructions", $instructions);
		$expectedString = '';
        $responseBody = $this->callAndCheckResponse($expectedString);
        /** @var QMStudy $s */
        $s = $responseBody->study;
        $html = $s->studyHtml->fullStudyHtml;
        $this->compareStudyHtml($html);
        $this->checkTestDuration(12);
		$this->checkQueryCount(28);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/study/create',
        'SERVER_NAME' => \App\Computers\ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT' => '443',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
        'HTTP_X_APP_VERSION' => '2.9.519',
        'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
        'HTTP_X_TIMEZONE' => 'America/Chicago',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '90',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '{"causeVariableName":"Folic Acid","effectVariableName":"Overall Mood","type":"individual"}',
        'slim.request.form_hash' =>
  [],
        'slim.request.query_hash' =>
  [
    'clientId' => 'quantimodo',
    'platform' => 'web',
  ],
        'responseStatusCode' => NULL,
        'unixtime' => 1561139425,
        'requestDuration' => 2.8902769088745117,
    ];
}
