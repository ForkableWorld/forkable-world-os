<?php /** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\B\Measurements;
use App\DataSources\Connectors\GoogleFitConnector;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\QMMeasurementExtended;
use Tests\SlimStagingTestCase;
class GetMeasurementsForGoogleFitConnectorTest extends SlimStagingTestCase
{
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testGetMeasurementsForGoogleFitConnector(): void{
		$expectedString = '';
        /** @var QMMeasurement[] $measurements */
        $measurements = $this->callAndCheckResponse($expectedString);
		$this->assertCount(50, $measurements);
        foreach($measurements as $m){
            /** @var QMMeasurementExtended $m */
		    $this->assertEquals(GoogleFitConnector::IMAGE, $m->pngPath);
            $this->assertEquals(GoogleFitConnector::IMAGE, $m->imageUrl);
            $this->assertEquals(GoogleFitConnector::DISPLAY_NAME, $m->sourceName);
        }
		$this->checkTestDuration(10);
		$this->checkQueryCount(5);
	}
	public $expectedResponseSizes = [];
	public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '192.168.10.1',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/measurements',
        'SERVER_NAME' => \App\Computers\ThisComputer::LOCAL_HOST_NAME,
        'SERVER_PORT' => '443',
        'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
        'HTTP_REFERER' => 'https://dev-web.quantimo.do/',
        'HTTP_SEC_FETCH_MODE' => 'cors',
        'HTTP_SEC_FETCH_SITE' => 'same-site',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'web',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer mike-test-token',
        'HTTP_X_APP_VERSION' => '2.9.1128',
        'HTTP_ORIGIN' => 'https://dev-web.quantimo.do',
        'HTTP_X_TIMEZONE' => 'America/Chicago',
        'HTTP_CACHE_CONTROL' => 'no-cache',
        'HTTP_PRAGMA' => 'no-cache',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
  [
    'sort' => '-startTimeEpoch',
    'limit' => '50',
    'offset' => '0',
    'connectorName' => 'googlefit',
    'doNotProcess' => 'true',
    'clientId' => 'quantimodo',
    'platform' => 'web',
  ],
        'slim.request.form_hash' =>
  [],
        'responseStatusCode' => 200,
        'unixtime' => 1575416742,
        'requestDuration' => 0,
    ];
}
