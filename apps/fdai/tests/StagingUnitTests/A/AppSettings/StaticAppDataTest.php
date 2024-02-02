<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\A\AppSettings;
use App\Logging\QMLog;
use App\Models\Application;
use App\PhpUnitJobs\Cleanup\AppCleanupJob;
use App\Properties\Base\BaseAccessTokenProperty;
use Tests\SlimStagingTestCase;

class StaticAppDataTest extends SlimStagingTestCase
{
    public function testStaticAppData(){
		$expectedString = 'medimodo';
        AppCleanupJob::deleteTestAppsCreatedMoreThan24HoursAgo();
        try {
            $response = $this->callAndCheckResponse($expectedString);
        } catch (\Throwable $e){
            QMLog::error(Application::generateDataLabIndexUrl().": ".$e->getMessage());
            throw $e;
        }
        $this->checkTestDuration(16);
        //$this->checkQueryCount(6);
	}
	public $expectedResponseSizes = [
      'staticData' => 3247
    ];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v1/appSettings',
        'QUERY_STRING' => 'clientId=medimodo&includeClientSecret=true&allStaticAppData=true&access_token='. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_CONNECTION' => 'close',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_USER_AGENT' => 'Request-Promise',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' => [
        'clientId' => 'medimodo',
        'includeClientSecret' => 'true',
        'allStaticAppData' => 'true',
        'access_token' => ''. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
      ],
        'slim.request.form_hash' => [],
        'responseStatusCode' => 200,
        'unixtime' => 1538075873,
    ];
}
