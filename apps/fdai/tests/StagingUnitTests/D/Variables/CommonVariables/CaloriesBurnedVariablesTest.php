<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\D\Variables\CommonVariables;
use App\Computers\ThisComputer;
use App\Models\Variable;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\CaloriesBurnedCommonVariable;
use App\Variables\QMCommonVariable;
use Tests\SlimStagingTestCase;
class CaloriesBurnedVariablesTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public $retry = true;
    public function testCaloriesBurnedVariables(){
		$v = Variable::findByName(CaloriesBurnedCommonVariable::NAME);
		$this->assertNotNull($v);
		$responseBody = $this->callAndCheckResponse(CaloriesBurnedCommonVariable::NAME);
		$this->checkTestDuration(18);
		$this->checkQueryCount(9);
		$calories = QMCommonVariable::findByNameIdOrSynonym(CaloriesBurnedCommonVariable::NAME);
		$this->assertEquals(BaseCombinationOperationProperty::COMBINATION_SUM, $calories->getOrSetCombinationOperation());
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = array (
  'REQUEST_METHOD' => 'GET',
  'REMOTE_ADDR' => '10.0.2.2',
  'SCRIPT_NAME' => '',
  'PATH_INFO' => '/api/v3/variables',
  'SERVER_NAME' => ThisComputer::LOCAL_HOST_NAME,
  'SERVER_PORT' => '443',
  'HTTP_X_FIRELOGGER' => '1.3',
  'HTTP_COOKIE' => 'driftt_aid=98a8a3d2-1d31-4327-9838-6eaec7f8a19f; DFTT_END_USER_PREV_BOOTSTRAPPED=true; PHPSESSID=4a0fo1tb87a790rkp5b66858av; XSRF-TOKEN=eyJpdiI6IkRBSitrSVJNaThnMWpRKzV3MG5yaVE9PSIsInZhbHVlIjoiRWFHa1o3dldxWDg4d1ppRzVzMTVNcnhJQXJTaU5jbFNWM2lEczVlR3ZEaU1jTHZHU2FRTVdBY0xoS3QwekI4ciIsIm1hYyI6ImY5MmFmMzBhMmY2NWNlNTNlMTU4OTVkYjk1MDA4MmUxZjQzOTY4MTE3YWVjZmM2ZmEwNGQ4NTFlZjk0MzgyMGEifQ%3D%3D; laravel_session=eyJpdiI6InpWSENpU0hZT0dUZzBFUm0yOTFKRVE9PSIsInZhbHVlIjoidzFvR1RqaGpkWG1OTVwvWFU0Y0ZFTGFENDdXWm1pVkhXWmFETEg4NHFXNlRkWkJERmxYYnlPd0JlclBTaW9aRkkiLCJtYWMiOiJkNTcwOGEyOWIzNThjMTkzM2QzZGZhN2VmNjBhNWZiZDRhNmRmNWI3YzY4ZmYwYjBhYWUxZTdjYTgxMGU2ODEyIn0%3D; __cfduid=dcec1b9e130ca6aaddab330d247f70aac1544585515; _ga=GA1.2.76774008.1544585528; _gid=GA1.2.1745856995.1544585528; XDEBUG_SESSION=PHPSTORM; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=testuser%7C1545953351%7C86db9a3d39d98100ae332be88d45d355%7Cquantimodo; final_callback_url=https%3A%2F%2Fquantimo.do%2F',
  'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
  'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
  'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
  'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36',
  'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
  'HTTP_CACHE_CONTROL' => 'max-age=0',
  'HTTP_CONNECTION' => 'keep-alive',
  'CONTENT_LENGTH' => '',
  'CONTENT_TYPE' => '',
  'slim.url_scheme' => 'https',
  'slim.input' => '',
  'slim.request.query_hash' =>
  array (
    'includeCharts' => 'true',
    'name' => 'Calories+Burned',
    'limit' => '50',
    'sort' => '-latestTaggedMeasurementTime',
    'clientId' => 'preve-wellness-tracker',
    'platform' => 'web',
  ),
  'slim.request.form_hash' =>
  array (
  ),
  'responseStatusCode' => NULL,
  'unixtime' => 1544826848,
  'requestDuration' => 7.5049779415130615,
);
}
