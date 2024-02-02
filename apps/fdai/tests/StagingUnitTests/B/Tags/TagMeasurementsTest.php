<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\B\Tags;
use App\Logging\QMLog;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Charts\UserVariableCharts\UserVariableChartGroup;
use App\Storage\Memory;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
use Tests\Traits\TestsCharts;
class TagMeasurementsTest extends SlimStagingTestCase {
	use TestsCharts;
    public $maximumResponseArrayLength = false;
    public $minimumResponseArrayLength = false;
    public function testTagMeasurements(){
        $lectins = QMUserVariable::findUserVariableByNameIdOrSynonym(230, 'Lectins');
        $this->assertGreaterThan($lectins->numberOfMeasurements,
            $lectins->numberOfRawMeasurementsWithTagsJoinsChildren);
        $lectins->analyzeFully("testing", true);
        $tagged = $lectins->getUserTaggedVariables();
        $this->assertGreaterThan(0, count($tagged));
        $measurements = $lectins->getValidDailyMeasurementsWithTagsAndFilling();
        $this->assertGreaterThan(0, count($measurements));
		$charts = $lectins->getChartGroup()->lineChartWithSmoothing;
		$this->compareHtmlPage("lectins", $lectins->getHtmlPage());
        Memory::resetClearOrDeleteAll();
        $expectedString = 'lectins';
        $responseBody = $this->callAndCheckResponse($expectedString);
        /** @var QMUserVariable $gotten */
        $gotten = $responseBody[0];
        $this->assertGreaterThan($gotten->numberOfMeasurements,
            $gotten->numberOfRawMeasurementsWithTagsJoinsChildren);
        /** @var UserVariableChartGroup $charts */
        $charts = $gotten->charts;
		$this->assertContains('Tomato', json_encode($charts), 
		                      QMLog::var_export($charts, true));
        $this->assertGreaterThan(600,
            count($charts->lineChartWithSmoothing->highchartConfig->series[3]->data));
        static::compareChart("lineChartWithSmoothing", $charts->lineChartWithSmoothing);
        $this->assertGreaterThan(0, count($gotten->userTaggedVariables));
        $this->checkTestDuration(34);
        $this->checkQueryCount(68);
    }
    public $expectedResponseSizes = [];
    public $slimEnvironmentSettings = [
        'REQUEST_METHOD' => 'GET',
        'REMOTE_ADDR' => '10.0.2.2',
        'SCRIPT_NAME' => '',
        'PATH_INFO' => '/api/v3/userVariables',
        'SERVER_NAME' => '_',
        'SERVER_PORT' => '443',
        'HTTP_COOKIE' => '__cfduid=da670b1a7b4c07dd8833aa9d13bcf07551520554796',
        'HTTP_ACCEPT_LANGUAGE' => 'en-us',
        'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
        'HTTP_X_FRAMEWORK' => 'ionic',
        'HTTP_X_PLATFORM' => 'android',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Linux; Android 8.1; ONEPLUS A5010 Build/OPM1.171019.011) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Crosswalk/22.52.561.4 Mobile Safari/537.36',
        'HTTP_X_CLIENT_ID' => 'quantimodo',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
        'HTTP_X_APP_VERSION' => '2.8.1028',
        'HTTP_CONNECTION' => 'keep-alive',
        'CONTENT_LENGTH' => '',
        'CONTENT_TYPE' => 'application/json',
        'slim.url_scheme' => 'https',
        'slim.input' => '',
        'slim.request.query_hash' =>
            [
                'includeCharts' => 'true',
                'name' => 'Lectins',
                'limit' => '50',
                'sort' => '-latestTaggedMeasurementTime',
                'clientId' => 'quantimodo',
                'platform' => 'android',
            ],
        'slim.request.form_hash' =>
            [
            ],
        'responseStatusCode' => NULL,
        'unixtime' => 1540911177,
        'requestDuration' => 10.54121994972229,
    ];
}
