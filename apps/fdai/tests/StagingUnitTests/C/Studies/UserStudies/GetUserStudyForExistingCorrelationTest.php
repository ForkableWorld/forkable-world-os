<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\StagingUnitTests\C\Studies\UserStudies;
use App\Charts\CorrelationCharts\UserVariableRelationshipScatterPlot;
use App\Charts\QMHighcharts\ScatterHighchartConfig;
use App\DataSources\Connectors\QuantiModoConnector;
use App\Properties\Base\BaseAccessTokenProperty;
use App\Studies\QMStudy;
use App\Variables\QMUserVariable;
use Tests\SlimStagingTestCase;
class GetUserStudyForExistingCorrelationTest extends SlimStagingTestCase {
    public function testGetUserStudyForExistingCorrelation(){
        $energy = QMUserVariable::getByNameOrId(230, 1306);
        $ds = $energy->getBestDataSource();
        $this->assertEquals(QuantiModoConnector::NAME, $ds->name);
		$expectedString = '';
        /** @var QMStudy $study */
        $study = $this->callAndCheckResponse($expectedString);
        $this->compareObjectFixture('participantInstructions', $study->participantInstructions);
        $charts = $study->studyCharts;
        /** @var UserVariableRelationshipScatterPlot $scatter */
        $scatter = $charts->correlationScatterPlot;
        /** @var ScatterHighchartConfig $hc */
        $hc = $scatter->highchartConfig;
        $this->assertNotEmpty($hc->xAxis->title->text);
        $this->assertNotEmpty($hc->yAxis->title->text);
		$this->assertContains("increase in 7 days cumulative Indoor CO2 is usually followed by an increase in Energy.",
            $scatter->explanation);
		$this->assertGreaterThan(1, count($study->studyCharts->predictorDistributionColumnChart->highchartConfig->series[0]->data));
		$this->checkTestDuration(14);
		$this->checkQueryCount(31);
	}
	public $expectedResponseSizes = [
		// Look at variable charts on variable page because it's too slow to get all the correlation charts
        'causeVariable'           => 15,
        'effectVariable' => 24.519, 
        'participantInstructions' => 20,
        'principalInvestigator' => 0.565,
        'statistics'              => 16.0,
        'studyCard'               => 20,
        'studyCharts'             => 210,
        'studyHtml'               => 916,
        'studyImages'             => 2.0,
        'studyLinks'              => 4,
        'studyText'               => 20,
    ];
	public $slimEnvironmentSettings = [
      'REQUEST_METHOD' => 'GET',
      'REMOTE_ADDR' => '10.0.2.2',
      'SCRIPT_NAME' => '',
      'PATH_INFO' => '/api/v4/study',
      'QUERY_STRING' => 'userId=230&clientId=quantimodo&includeCharts=true&platform=web&studyId=cause-6034982-effect-1306-user-230-user-study',
      'SERVER_NAME' => '_',
      'SERVER_PORT' => '443',
      'HTTP_X_FIRELOGGER' => '1.3',
      'HTTP_COOKIE' => 'XDEBUG_SESSION=PHPSTORM; _ga=GA1.2.956197214.1538009354; __cfduid=d1d1a0e2822985ef9d386e30f478657f01538012107; final_callback_url=https%3A%2F%2Flocal.quantimo.do%2Fionic%2FModo%2Fsrc%2Findex.html%23%2Fapp%2Flogin%3Ffinal_callback_url%3Dhttps%253A%252F%252Flocal.quantimo.do%252Fionic%252FModo%252Fsrc%252Findex.html%2523%252Fapp%252Flogin%26clientId%3Dquantimodo%26clientSecret%3DTcQArZOoUecO9O4aBvntUl6v1QzzsU38%26message%3DConnected%2BGoogle%2BPlus%2521; quantimodo_logged_in_af6160480df78a3a6d520187243f05c9=mike%7C1539234481%7Cde069cf4b3bbf933721060a76259dad7%7Cquantimodo; _gid=GA1.2.1384267982.1538253686; _gat=1',
      'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
      'HTTP_ACCEPT_ENCODING' => 'gzip, deflate, br',
      'HTTP_REFERER' => 'https://local.quantimo.do/ionic/Modo/src/index.html',
      'HTTP_X_FRAMEWORK' => 'ionic',
      'HTTP_X_PLATFORM' => 'web',
      'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
      'HTTP_X_CLIENT_ID' => 'quantimodo',
      'HTTP_ACCEPT' => 'application/json',
      'HTTP_CONTENT_TYPE' => 'application/json',
      'HTTP_AUTHORIZATION' => 'Bearer '. BaseAccessTokenProperty::ADMIN_TEST_TOKEN,
      'HTTP_X_APP_VERSION' => '2.8.929',
      'HTTP_CACHE_CONTROL' => 'no-cache',
      'HTTP_PRAGMA' => 'no-cache',
      'HTTP_CONNECTION' => 'keep-alive',
      'CONTENT_LENGTH' => '',
      'CONTENT_TYPE' => 'application/json',
      'slim.url_scheme' => 'https',
      'slim.input' => '',
      'slim.request.query_hash' => [
        'userId' => '230',
        'clientId' => 'quantimodo',
        'includeCharts' => 'true',
        'platform' => 'web',
        'studyId' => 'cause-6034982-effect-1306-user-230-user-study',
      ],
      'responseStatusCode' => 200,
      'unixtime' => 1538255741,
    ];
}
