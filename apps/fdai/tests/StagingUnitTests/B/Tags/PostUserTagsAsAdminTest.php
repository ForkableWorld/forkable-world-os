<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\Tags;
use App\Computers\ThisComputer;
use App\Logging\QMLog;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\Middleware\QMAuth;
use App\Variables\QMUserTag;
use Tests\SlimStagingTestCase;
use Throwable;

class PostUserTagsAsAdminTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    //public $expectedCode = 400;  // Tag should already exist
    public function testPostUserTagsAsAdmin(): void{
        ThisComputer::logMemoryUsage();
		QMAuth::loginMike();
        try {
            QMUserTag::handleDeleteUserTagRequest([
                "userTagVariableId"    => 5983282,
                "userTaggedVariableId" => 6048868,
                "conversionFactor"     => 1
            ]);
        } catch (Throwable $e) {
            QMLog::info(__METHOD__.": ".$e->getMessage());
        }
		$responseBody = $this->callAndCheckResponse('');
        //$this->callAndCheckResponse( 'Tag already exists');
		$this->checkTestDuration(17);
		$this->checkQueryCount(62);
	}
	public $expectedResponseSizes = [
      'success' => 0.004,
      'status' => 0.012,
      'description' => 0.312,
      'summary' => 0.047,
    ];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'POST',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/userTags',
        'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT' => '443',
        'HTTP_X_FIRELOGGER' => '1.3',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
        'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '83',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '[{"userTagVariableId":5983282,"userTaggedVariableId":6048868,"conversionFactor":1}]',
        'slim.request.form_hash' =>
  [],
        'slim.request.query_hash' =>
  [
    'appName' => 'QuantiModo',
    'appVersion' => '2.9.202',
    'clientId' => 'quantimodo',
  ],
        'responseStatusCode' => 201,
        'unixtime' => 1549212980,
        'requestDuration' => 1.485817193985,
    ];
}
