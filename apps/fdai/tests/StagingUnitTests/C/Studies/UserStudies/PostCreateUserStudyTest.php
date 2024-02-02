<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Studies\QMStudy;
use App\Studies\StudyText;
use Tests\SlimStagingTestCase;
class PostCreateUserStudyTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testPostCreateUserStudyTest(): void{
        $causeVariableNameOrId = "Outdoor Humidity";
        $effectVariableNameOrId = "Overall Mood";
        QMUserVariableRelationship::getOrCreateUserVariableRelationship(230, $causeVariableNameOrId,
            $effectVariableNameOrId);
		$expectedString = '';
		$this->slimEnvironmentSettings = [
            'REQUEST_METHOD' => 'POST',
            'REMOTE_ADDR' => '24.216.168.142',
            'SCRIPT_NAME' => '',
            'PATH_INFO' => '/api/v3/study/create',
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
            'HTTP_X_APP_VERSION' => '2.10.218',
            'HTTP_X_TIMEZONE' => 'America/Chicago',
            'HTTP_CACHE_CONTROL' => 'no-cache',
            'HTTP_PRAGMA' => 'no-cache',
            'HTTP_CONNECTION' => 'keep-alive',
            'CONTENT_LENGTH' => '96',
            'CONTENT_TYPE' => 'application/json',
            'slim.url_scheme' => 'https',
            'slim.input' => '{"causeVariableName":"Outdoor Humidity","effectVariableName":"Overall Mood","type":"individual"}',
            'slim.request.form_hash' =>
  [],
            'slim.request.query_hash' =>
  [
    'clientId' => 'quantimodo',
    'platform' => 'web',
  ],
            'responseStatusCode' => NULL,
            'unixtime' => 1582007314,
            'requestDuration' => 2.3033668994903564,
        ];
		$responseBody = $this->callAndCheckResponse($expectedString);
        /** @var QMStudy $s */
        $s = $responseBody->study;
        $h = $s->studyHtml;
        /** @var StudyText $t */
        $t = $s->studyText;
		$this->assertNotContains("Does ", $h->studyTitleHtml);
        $this->assertContains($effectVariableNameOrId, $h->studyTitleHtml);
        $this->assertContains($causeVariableNameOrId, $h->studyTitleHtml);
        $this->assertNotContains("Donate ", $h->tagLineHtml);
        $this->assertContains($t->tagLine, $h->tagLineHtml);
		$this->checkTestDuration(28);
		$this->checkQueryCount(27);
	}
	public $expectedResponseSizes = [];
}
