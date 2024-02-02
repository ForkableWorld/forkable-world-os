<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpDocSignatureInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\Measurements;
use App\Computers\ThisComputer;
use App\Logging\QMLog;
use App\Models\Variable;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use App\VariableCategories\PhysicalActivityVariableCategory;
use Tests\SlimStagingTestCase;
class GetMeasurementsForCategoryTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
	/**
	 * @return void
	 * @covers \App\Slim\Controller\Measurement\GetMeasurementController
	 */
	public function testGetMeasurementsForCategory(){
        $variable = Variable::findByNameOrId("Resting Heart Rate (Pulse)");
        $variable->analyzeFullyIfNecessary(__FUNCTION__);
		$expectedString = '';
		$this->setAuthenticatedUser(1);
		$responseBody = $this->callAndCheckResponse($expectedString);
        /** @var QMMeasurementExtended $measurement */
        foreach($responseBody as $measurement){
            if($measurement->variableCategoryName !== PhysicalActivityVariableCategory::NAME){
                QMLog::error(QMLog::var_export($measurement, true));
            }
		    $this->assertEquals(PhysicalActivityVariableCategory::NAME, $measurement->variableCategoryName);
        }
		$this->checkTestDuration(8);
		$this->checkQueryCount(6);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/measurements',
        'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT' => '443',
        'HTTP_X_FIRELOGGER' => '1.3',
        'HTTP_COOKIE' => 'driftt_aid=98a8a3d2-1d31-4327-9838-6eaec7f8a19f; DFTT_END_USER_PREV_BOOTSTRAPPED=true; PHPSESSID=4a0fo1tb87a790rkp5b66858av; XSRF-TOKEN=eyJpdiI6IkRBSitrSVJNaThnMWpRKzV3MG5yaVE9PSIsInZhbHVlIjoiRWFHa1o3dldxWDg4d1ppRzVzMTVNcnhJQXJTaU5jbFNWM2lEczVlR3ZEaU1jTHZHU2FRTVdBY0xoS3QwekI4ciIsIm1hYyI6ImY5MmFmMzBhMmY2NWNlNTNlMTU4OTVkYjk1MDA4MmUxZjQzOTY4MTE3YWVjZmM2ZmEwNGQ4NTFlZjk0MzgyMGEifQ%3D%3D; laravel_session=eyJpdiI6InpWSENpU0hZT0dUZzBFUm0yOTFKRVE9PSIsInZhbHVlIjoidzFvR1RqaGpkWG1OTVwvWFU0Y0ZFTGFENDdXWm1pVkhXWmFETEg4NHFXNlRkWkJERmxYYnlPd0JlclBTaW9aRkkiLCJtYWMiOiJkNTcwOGEyOWIzNThjMTkzM2QzZGZhN2VmNjBhNWZiZDRhNmRmNWI3YzY4ZmYwYjBhYWUxZTdjYTgxMGU2ODEyIn0%3D; __cfduid=dcec1b9e130ca6aaddab330d247f70aac1544585515; _ga=GA1.2.76774008.1544585528; _gid=GA1.2.1745856995.1544585528; final_callback_url=https%3A%2F%2Fquantimodo.quantimo.do%2Fionic%2FModo%2Fwww%2Findex.html%23%2Fapp%2Flogin%3Fmessage%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1545891993%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; XDEBUG_SESSION=PHPSTORM',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::PERMANENT_TEST_USER_ACCESS_TOKEN_FOR_18535,
        'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
        'HTTP_CACHE_CONTROL' => 'max-age=0',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => '',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
  [
    'sort' => '-startTimeEpoch',
    'limit' => '50',
    'offset' => '0',
    'variableCategoryName' => 'Physical+Activity',
    'doNotProcess' => 'true',
    'clientId' => 'preve-wellness-tracker',
    'platform' => 'web',
  ],
        'slim.request.form_hash' =>
  [],
        'responseStatusCode' => NULL,
        'unixtime' => 1544730649,
        'requestDuration' => 42.64355492591858,
    ];
}
