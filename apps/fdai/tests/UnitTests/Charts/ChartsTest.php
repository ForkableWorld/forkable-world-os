<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\UnitTests\Charts;
use App\Charts\HighchartExport;
use App\Models\User;
use App\Models\UserVariable;
use App\Storage\DB\TestDB;
use App\Types\QMStr;
use App\Utils\AppMode;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\CommonVariables\SymptomsCommonVariables\BackPainCommonVariable;
use App\Widgets\LastLoginChartWidget;
use App\Charts\QMChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\UserVariableCharts\UserVariableChartGroup;

use Carbon\Carbon;
use Tests\Traits\TestsCharts;
use Tests\UnitTestCase;
class ChartsTest extends UnitTestCase
{

	use TestsCharts;
    protected function setUp(): void{
        parent::setUp();
	    TestDB::resetUserTables();
    }
    public function testLastLoginChart(){
        $time = Carbon::createFromDate(2020, 1, 1);
        $users = User::all();
        foreach($users as $user){
            $user->last_login_at = $time;
            $user->save();
            $time->addDays(1);
        }
        $this->assertGreaterThan(0, $users->count());
        $widget = new LastLoginChartWidget();
        $chart = $widget->getHighchart();
        $data = $chart->getRawData();
        foreach($data as $datum){$this->assertNotNull($datum->date);}
        $this->assertGreaterThanOrEqual($users->count(), count($data));
        $html = $widget->getHtml();
        $html = QMStr::replace_between($html, "Date.UTC(", "),", "2020, 10, 10");
        $this->compareHtmlFragment('LastLoginChart', $html);
        $this->assertNotContains("Wp Users", $html, '', true);
    }
    public function testExportDualLineChart(){
		$config = $this->getDualLineChartConfig();
		$config->type = 'Dual Line Chart';
        $export = new HighchartExport($config);
        $this->assertEquals('CauseVariableName Intake & EffectVariableName Over Time', $export->getTitleAttribute());
        if(AppMode::isWindows()){
            return;
        }
        $data = $export->getImageData();
        $this->assertNotNull($data);
        $html = $export->getHtml();
        $this->assertNotNull($html);
    }
	public function testRemoteHighchartExport(){
		$this->skipTest("TODO: Fix this test");
		$mood = UserVariable::findByNameOrId(1, OverallMoodCommonVariable::NAME);
		$charts = $mood->getChartGroup();
		$chart = $charts->lineChartWithSmoothing;
		$export = new HighchartExport($chart);
		$response = $export->exportRemotely(HighchartExport::PNG);
		$this->assertStringNotContainsString("went wrong", $response);
		$this->compareFile('RemoteHighchartExport.png', $response, false);
		//$this->assertEquals("", $response);
	}
    public function testLocalHighchartExport(){
	    if(AppMode::isWindows()){
		    $this->skipTest("Skipping this test on Windows");
	    }
        $userVariable = $this->getOverallMoodUserVariable(1);
        $charts = $userVariable->getChartGroup();
        $this->assertNotNull($charts);
        $charts->outputImageSizesByType();
        $this->checkLineChartExportConfig($charts);
        $this->checkMonthChart($charts);
        $html = $charts->getChartHtmlWithEmbeddedImages();
        $this->assertContains("Average Overall Mood by Year", $html);
    }
	public function postBackPainMeasurements(): ?UserVariable {
		$this->setAuthenticatedUser(1);
		$postData = '[{"measurements":[{"startTime":1406419860,"value":"1"},{"startTime":1406519965,"value":"3"}],
            "name":"Back Pain","source":"test source name","category":"Symptoms","combinationOperation":"MEAN","unit":"/5"}]';
		$this->postApiV3('measurements', $postData);
		return UserVariable::findByNameOrId(1, BackPainCommonVariable::NAME);
	}
    public function testGetChartsByVariableName(){
        $this->setAuthenticatedUser(1);
        $variableName = OverallMoodCommonVariable::NAME;
        $uv = $this->getApiV6UserVariable($variableName);
        $this->assertNotNull($uv);
        $v = $uv->variable;
        $this->assertEquals($variableName, $v->name);
        $charts = $uv->charts;
        //$this->assertCount(6, $charts);
        /** @var UserVariableChartGroup $charts */
        $this->assertNotNull($charts->distributionColumnChart);
        $this->assertNotNull($charts->weekdayColumnChart);
        $this->assertNotNull($charts->monthlyColumnChart);
        $this->assertNotNull($charts->lineChartWithSmoothing);
        $this->assertNotNull($charts->correlationsSankeyChart);
        static::compareChart('correlationsSankeyChart', $charts->correlationsSankeyChart);
        $this->compareChartGroup($charts);
        //$this->assertNotNull($charts->lineChartWithoutSmoothing);
    }
    /**
     * @param QMChart $chart
     */
    public function checkHighchartExportConfig(QMChart $chart){
        $actual = $chart->getExportableConfig();
        $html = $chart->generateEmbeddedImageHtml(HighchartExport::PNG);
        $this->compareHtmlFragment('ImageHtml', $html);
        $html = $chart->getHighchartConfig()->getHtml(false);
        $this->compareHtmlFragment('dynamic-'.$chart->getId(), $html);
        $this->compareObjectFixture(__FUNCTION__, $actual,"
CHECK EXPORTED IMAGE AT:
If it looks good commit new version to repo");
    }
    /**
     * @param QMChart $chart
     */
    public function checkHighchartDynamicConfig(QMChart $chart){
        $actual = $chart->getHighchartConfig();
        $url = $chart->saveDynamicHtmlLocally();
        $this->compareObjectFixture(__FUNCTION__.'-'.$chart->id, $actual,
            "Check $url and if it looks good replace");
    }
    /**
     * @param UserVariableChartGroup $charts
     */
    public function checkLineChartExportConfig(UserVariableChartGroup $charts): void{
        $overTime = $charts->getLineChartWithSmoothing();
        $html = $overTime->getOrGenerateEmbeddedImageHtml("png");
        $this->assertContains("Over Time", $html);
        $this->checkHighchartExportConfig($overTime);
        $this->checkHighchartDynamicConfig($overTime);
    }
    /**
     * @param UserVariableChartGroup $charts
     */
    public function checkMonthChart(UserVariableChartGroup $charts): void{
        $byMonth = $charts->getMonthlyColumnChart();
        $this->compareObjectFixture(__FUNCTION__."-exportable-config-$byMonth->id", $byMonth->getExportableConfig());
        $config = $byMonth->getHighchartConfig();
        $url = $config->saveHtmlLocally();
        $this->compareObjectFixture(__FUNCTION__."-regular-config-$byMonth->id", $config,
            "Check $url and if it looks good replace");
    }
    private function getDualLineChartConfig(): HighchartConfig{
        $config =  HighchartConfig::__set_state([
				'type' => 'Line Chart',
                'minMaxBuffer' => NULL,
                'subtitle' =>
                    [
                        'text' => 'Blue represents the sum of CauseVariableName intake over the previous 24 hours',
                    ],
                'chart' =>
                    [
                        'renderTo' => 'container',
                        'plotBackgroundImage' => false,
                    ],
                'colors' =>
                    [
                        0 => '#000000',
                        1 => '#5D83FF',
                        2 => '#68B107',
                        3 => '#ffbd40',
                        4 => '#CB0000',
                    ],
                'credits' =>
                    [
                        'enabled' => false,
                    ],
                'lang' =>
                    [
                        'loading' => '',
                    ],
                'legend' =>
                    [
                        'enabled' => true,
                    ],
                'loading' => NULL,
                'plotOptions' =>
                    [
                        'series' =>
                            [],
                    ],
                'series' =>
                    [
                        0 =>
                            [
                                'data' =>
                                    [
                                        0 =>
                                            [
                                                0 => 1348159020000,
                                                1 => 25.0,
                                            ],
                                        1 =>
                                            [
                                                0 => 1348245420000,
                                                1 => 3.0,
                                            ],
                                        2 =>
                                            [
                                                0 => 1348331820000,
                                                1 => 34.0,
                                            ],
                                        3 =>
                                            [
                                                0 => 1348418220000,
                                                1 => 71.0,
                                            ],
                                        4 =>
                                            [
                                                0 => 1348504620000,
                                                1 => 89.0,
                                            ],
                                        5 =>
                                            [
                                                0 => 1348591020000,
                                                1 => 1.0,
                                            ],
                                        6 =>
                                            [
                                                0 => 1348677420000,
                                                1 => 12.0,
                                            ],
                                        7 =>
                                            [
                                                0 => 1348763820000,
                                                1 => 79.0,
                                            ],
                                        8 =>
                                            [
                                                0 => 1348850220000,
                                                1 => 92.0,
                                            ],
                                        9 =>
                                            [
                                                0 => 1348936620000,
                                                1 => 29.0,
                                            ],
                                        10 =>
                                            [
                                                0 => 1349023020000,
                                                1 => 95.0,
                                            ],
                                        11 =>
                                            [
                                                0 => 1349109420000,
                                                1 => 37.0,
                                            ],
                                        12 =>
                                            [
                                                0 => 1349195820000,
                                                1 => 83.0,
                                            ],
                                        13 =>
                                            [
                                                0 => 1349282220000,
                                                1 => 77.0,
                                            ],
                                        14 =>
                                            [
                                                0 => 1349368620000,
                                                1 => 19.0,
                                            ],
                                        15 =>
                                            [
                                                0 => 1349455020000,
                                                1 => 55.0,
                                            ],
                                        16 =>
                                            [
                                                0 => 1349541420000,
                                                1 => 36.0,
                                            ],
                                        17 =>
                                            [
                                                0 => 1349627820000,
                                                1 => 24.0,
                                            ],
                                        18 =>
                                            [
                                                0 => 1349714220000,
                                                1 => 98.0,
                                            ],
                                        19 =>
                                            [
                                                0 => 1349800620000,
                                                1 => 78.0,
                                            ],
                                        20 =>
                                            [
                                                0 => 1349887020000,
                                                1 => 51.0,
                                            ],
                                        21 =>
                                            [
                                                0 => 1349973420000,
                                                1 => 83.0,
                                            ],
                                        22 =>
                                            [
                                                0 => 1350059820000,
                                                1 => 56.0,
                                            ],
                                        23 =>
                                            [
                                                0 => 1350146220000,
                                                1 => 95.0,
                                            ],
                                        24 =>
                                            [
                                                0 => 1350232620000,
                                                1 => 74.0,
                                            ],
                                        25 =>
                                            [
                                                0 => 1350319020000,
                                                1 => 54.0,
                                            ],
                                        26 =>
                                            [
                                                0 => 1350405420000,
                                                1 => 21.0,
                                            ],
                                        27 =>
                                            [
                                                0 => 1350491820000,
                                                1 => 9.0,
                                            ],
                                        28 =>
                                            [
                                                0 => 1350578220000,
                                                1 => 3.0,
                                            ],
                                        29 =>
                                            [
                                                0 => 1350664620000,
                                                1 => 63.0,
                                            ],
                                        30 =>
                                            [
                                                0 => 1350751020000,
                                                1 => 24.0,
                                            ],
                                        31 =>
                                            [
                                                0 => 1350837420000,
                                                1 => 56.0,
                                            ],
                                        32 =>
                                            [
                                                0 => 1350923820000,
                                                1 => 91.0,
                                            ],
                                        33 =>
                                            [
                                                0 => 1351010220000,
                                                1 => 60.0,
                                            ],
                                        34 =>
                                            [
                                                0 => 1351096620000,
                                                1 => 49.0,
                                            ],
                                        35 =>
                                            [
                                                0 => 1351183020000,
                                                1 => 56.0,
                                            ],
                                        36 =>
                                            [
                                                0 => 1351269420000,
                                                1 => 3.0,
                                            ],
                                        37 =>
                                            [
                                                0 => 1351355820000,
                                                1 => 53.0,
                                            ],
                                        38 =>
                                            [
                                                0 => 1351442220000,
                                                1 => 89.0,
                                            ],
                                        39 =>
                                            [
                                                0 => 1351528620000,
                                                1 => 73.0,
                                            ],
                                        40 =>
                                            [
                                                0 => 1351615020000,
                                                1 => 30.0,
                                            ],
                                        41 =>
                                            [
                                                0 => 1351701420000,
                                                1 => 45.0,
                                            ],
                                        42 =>
                                            [
                                                0 => 1351787820000,
                                                1 => 97.0,
                                            ],
                                        43 =>
                                            [
                                                0 => 1351874220000,
                                                1 => 41.0,
                                            ],
                                        44 =>
                                            [
                                                0 => 1351960620000,
                                                1 => 24.0,
                                            ],
                                        45 =>
                                            [
                                                0 => 1352047020000,
                                                1 => 4.0,
                                            ],
                                        46 =>
                                            [
                                                0 => 1352133420000,
                                                1 => 1.0,
                                            ],
                                        47 =>
                                            [
                                                0 => 1352219820000,
                                                1 => 11.0,
                                            ],
                                        48 =>
                                            [
                                                0 => 1352306220000,
                                                1 => 64.0,
                                            ],
                                        49 =>
                                            [
                                                0 => 1352392620000,
                                                1 => 92.0,
                                            ],
                                        50 =>
                                            [
                                                0 => 1352479020000,
                                                1 => 0.0,
                                            ],
                                        51 =>
                                            [
                                                0 => 1352565420000,
                                                1 => 20.0,
                                            ],
                                        52 =>
                                            [
                                                0 => 1352651820000,
                                                1 => 19.0,
                                            ],
                                        53 =>
                                            [
                                                0 => 1352738220000,
                                                1 => 2.0,
                                            ],
                                        54 =>
                                            [
                                                0 => 1352824620000,
                                                1 => 40.0,
                                            ],
                                        55 =>
                                            [
                                                0 => 1352911020000,
                                                1 => 79.0,
                                            ],
                                        56 =>
                                            [
                                                0 => 1352997420000,
                                                1 => 49.0,
                                            ],
                                        57 =>
                                            [
                                                0 => 1353083820000,
                                                1 => 56.0,
                                            ],
                                        58 =>
                                            [
                                                0 => 1353170220000,
                                                1 => 32.0,
                                            ],
                                        59 =>
                                            [
                                                0 => 1353256620000,
                                                1 => 67.0,
                                            ],
                                        60 =>
                                            [
                                                0 => 1353343020000,
                                                1 => 95.0,
                                            ],
                                        61 =>
                                            [
                                                0 => 1353429420000,
                                                1 => 53.0,
                                            ],
                                        62 =>
                                            [
                                                0 => 1353515820000,
                                                1 => 85.0,
                                            ],
                                        63 =>
                                            [
                                                0 => 1353602220000,
                                                1 => 45.0,
                                            ],
                                        64 =>
                                            [
                                                0 => 1353688620000,
                                                1 => 36.0,
                                            ],
                                        65 =>
                                            [
                                                0 => 1353775020000,
                                                1 => 53.0,
                                            ],
                                        66 =>
                                            [
                                                0 => 1353861420000,
                                                1 => 10.0,
                                            ],
                                        67 =>
                                            [
                                                0 => 1353947820000,
                                                1 => 56.0,
                                            ],
                                        68 =>
                                            [
                                                0 => 1354034220000,
                                                1 => 80.0,
                                            ],
                                        69 =>
                                            [
                                                0 => 1354120620000,
                                                1 => 70.0,
                                            ],
                                        70 =>
                                            [
                                                0 => 1354207020000,
                                                1 => 51.0,
                                            ],
                                        71 =>
                                            [
                                                0 => 1354293420000,
                                                1 => 17.0,
                                            ],
                                        72 =>
                                            [
                                                0 => 1354379820000,
                                                1 => 21.0,
                                            ],
                                        73 =>
                                            [
                                                0 => 1354466220000,
                                                1 => 92.0,
                                            ],
                                        74 =>
                                            [
                                                0 => 1354552620000,
                                                1 => 32.0,
                                            ],
                                        75 =>
                                            [
                                                0 => 1354639020000,
                                                1 => 46.0,
                                            ],
                                        76 =>
                                            [
                                                0 => 1354725420000,
                                                1 => 71.0,
                                            ],
                                        77 =>
                                            [
                                                0 => 1354811820000,
                                                1 => 66.0,
                                            ],
                                        78 =>
                                            [
                                                0 => 1354898220000,
                                                1 => 81.0,
                                            ],
                                        79 =>
                                            [
                                                0 => 1354984620000,
                                                1 => 30.0,
                                            ],
                                        80 =>
                                            [
                                                0 => 1355071020000,
                                                1 => 41.0,
                                            ],
                                        81 =>
                                            [
                                                0 => 1355157420000,
                                                1 => 83.0,
                                            ],
                                        82 =>
                                            [
                                                0 => 1355243820000,
                                                1 => 19.0,
                                            ],
                                        83 =>
                                            [
                                                0 => 1355330220000,
                                                1 => 0.0,
                                            ],
                                        84 =>
                                            [
                                                0 => 1355416620000,
                                                1 => 1.0,
                                            ],
                                        85 =>
                                            [
                                                0 => 1355503020000,
                                                1 => 45.0,
                                            ],
                                        86 =>
                                            [
                                                0 => 1355589420000,
                                                1 => 70.0,
                                            ],
                                        87 =>
                                            [
                                                0 => 1355675820000,
                                                1 => 54.0,
                                            ],
                                        88 =>
                                            [
                                                0 => 1355762220000,
                                                1 => 27.0,
                                            ],
                                        89 =>
                                            [
                                                0 => 1355848620000,
                                                1 => 66.0,
                                            ],
                                        90 =>
                                            [
                                                0 => 1355935020000,
                                                1 => 91.0,
                                            ],
                                        91 =>
                                            [
                                                0 => 1356021420000,
                                                1 => 17.0,
                                            ],
                                        92 =>
                                            [
                                                0 => 1356107820000,
                                                1 => 83.0,
                                            ],
                                        93 =>
                                            [
                                                0 => 1356194220000,
                                                1 => 12.0,
                                            ],
                                        94 =>
                                            [
                                                0 => 1356280620000,
                                                1 => 60.0,
                                            ],
                                        95 =>
                                            [
                                                0 => 1356367020000,
                                                1 => 11.0,
                                            ],
                                        96 =>
                                            [
                                                0 => 1356453420000,
                                                1 => 30.0,
                                            ],
                                        97 =>
                                            [
                                                0 => 1356539820000,
                                                1 => 50.0,
                                            ],
                                        98 =>
                                            [
                                                0 => 1356626220000,
                                                1 => 85.0,
                                            ],
                                    ],
                                'id' => 'predictor',
                                'name' => 'CauseVariableName intake (mg)',
                                'showInLegend' => true,
                                'tooltip' =>
                                    [
                                        'valueSuffix' => 'mg',
                                    ],
                                'yAxis' => 0,
                            ],
                        1 =>
                            [
                                'data' =>
                                    [
                                        0 =>
                                            [
                                                0 => 1348159020000,
                                                1 => 3.0,
                                            ],
                                        1 =>
                                            [
                                                0 => 1348245420000,
                                                1 => 34.0,
                                            ],
                                        2 =>
                                            [
                                                0 => 1348331820000,
                                                1 => 71.0,
                                            ],
                                        3 =>
                                            [
                                                0 => 1348418220000,
                                                1 => 89.0,
                                            ],
                                        4 =>
                                            [
                                                0 => 1348504620000,
                                                1 => 1.0,
                                            ],
                                        5 =>
                                            [
                                                0 => 1348591020000,
                                                1 => 12.0,
                                            ],
                                        6 =>
                                            [
                                                0 => 1348677420000,
                                                1 => 79.0,
                                            ],
                                        7 =>
                                            [
                                                0 => 1348763820000,
                                                1 => 92.0,
                                            ],
                                        8 =>
                                            [
                                                0 => 1348850220000,
                                                1 => 29.0,
                                            ],
                                        9 =>
                                            [
                                                0 => 1348936620000,
                                                1 => 95.0,
                                            ],
                                        10 =>
                                            [
                                                0 => 1349023020000,
                                                1 => 37.0,
                                            ],
                                        11 =>
                                            [
                                                0 => 1349109420000,
                                                1 => 83.0,
                                            ],
                                        12 =>
                                            [
                                                0 => 1349195820000,
                                                1 => 77.0,
                                            ],
                                        13 =>
                                            [
                                                0 => 1349282220000,
                                                1 => 19.0,
                                            ],
                                        14 =>
                                            [
                                                0 => 1349368620000,
                                                1 => 55.0,
                                            ],
                                        15 =>
                                            [
                                                0 => 1349455020000,
                                                1 => 36.0,
                                            ],
                                        16 =>
                                            [
                                                0 => 1349541420000,
                                                1 => 24.0,
                                            ],
                                        17 =>
                                            [
                                                0 => 1349627820000,
                                                1 => 98.0,
                                            ],
                                        18 =>
                                            [
                                                0 => 1349714220000,
                                                1 => 78.0,
                                            ],
                                        19 =>
                                            [
                                                0 => 1349800620000,
                                                1 => 51.0,
                                            ],
                                        20 =>
                                            [
                                                0 => 1349887020000,
                                                1 => 83.0,
                                            ],
                                        21 =>
                                            [
                                                0 => 1349973420000,
                                                1 => 56.0,
                                            ],
                                        22 =>
                                            [
                                                0 => 1350059820000,
                                                1 => 95.0,
                                            ],
                                        23 =>
                                            [
                                                0 => 1350146220000,
                                                1 => 74.0,
                                            ],
                                        24 =>
                                            [
                                                0 => 1350232620000,
                                                1 => 54.0,
                                            ],
                                        25 =>
                                            [
                                                0 => 1350319020000,
                                                1 => 21.0,
                                            ],
                                        26 =>
                                            [
                                                0 => 1350405420000,
                                                1 => 9.0,
                                            ],
                                        27 =>
                                            [
                                                0 => 1350491820000,
                                                1 => 3.0,
                                            ],
                                        28 =>
                                            [
                                                0 => 1350578220000,
                                                1 => 63.0,
                                            ],
                                        29 =>
                                            [
                                                0 => 1350664620000,
                                                1 => 24.0,
                                            ],
                                        30 =>
                                            [
                                                0 => 1350751020000,
                                                1 => 56.0,
                                            ],
                                        31 =>
                                            [
                                                0 => 1350837420000,
                                                1 => 91.0,
                                            ],
                                        32 =>
                                            [
                                                0 => 1350923820000,
                                                1 => 60.0,
                                            ],
                                        33 =>
                                            [
                                                0 => 1351010220000,
                                                1 => 49.0,
                                            ],
                                        34 =>
                                            [
                                                0 => 1351096620000,
                                                1 => 56.0,
                                            ],
                                        35 =>
                                            [
                                                0 => 1351183020000,
                                                1 => 3.0,
                                            ],
                                        36 =>
                                            [
                                                0 => 1351269420000,
                                                1 => 53.0,
                                            ],
                                        37 =>
                                            [
                                                0 => 1351355820000,
                                                1 => 89.0,
                                            ],
                                        38 =>
                                            [
                                                0 => 1351442220000,
                                                1 => 73.0,
                                            ],
                                        39 =>
                                            [
                                                0 => 1351528620000,
                                                1 => 30.0,
                                            ],
                                        40 =>
                                            [
                                                0 => 1351615020000,
                                                1 => 45.0,
                                            ],
                                        41 =>
                                            [
                                                0 => 1351701420000,
                                                1 => 97.0,
                                            ],
                                        42 =>
                                            [
                                                0 => 1351787820000,
                                                1 => 41.0,
                                            ],
                                        43 =>
                                            [
                                                0 => 1351874220000,
                                                1 => 24.0,
                                            ],
                                        44 =>
                                            [
                                                0 => 1351960620000,
                                                1 => 4.0,
                                            ],
                                        45 =>
                                            [
                                                0 => 1352047020000,
                                                1 => 1.0,
                                            ],
                                        46 =>
                                            [
                                                0 => 1352133420000,
                                                1 => 11.0,
                                            ],
                                        47 =>
                                            [
                                                0 => 1352219820000,
                                                1 => 64.0,
                                            ],
                                        48 =>
                                            [
                                                0 => 1352306220000,
                                                1 => 92.0,
                                            ],
                                        49 =>
                                            [
                                                0 => 1352392620000,
                                                1 => 0.0,
                                            ],
                                        50 =>
                                            [
                                                0 => 1352479020000,
                                                1 => 20.0,
                                            ],
                                        51 =>
                                            [
                                                0 => 1352565420000,
                                                1 => 19.0,
                                            ],
                                        52 =>
                                            [
                                                0 => 1352651820000,
                                                1 => 2.0,
                                            ],
                                        53 =>
                                            [
                                                0 => 1352738220000,
                                                1 => 40.0,
                                            ],
                                        54 =>
                                            [
                                                0 => 1352824620000,
                                                1 => 79.0,
                                            ],
                                        55 =>
                                            [
                                                0 => 1352911020000,
                                                1 => 49.0,
                                            ],
                                        56 =>
                                            [
                                                0 => 1352997420000,
                                                1 => 56.0,
                                            ],
                                        57 =>
                                            [
                                                0 => 1353083820000,
                                                1 => 32.0,
                                            ],
                                        58 =>
                                            [
                                                0 => 1353170220000,
                                                1 => 67.0,
                                            ],
                                        59 =>
                                            [
                                                0 => 1353256620000,
                                                1 => 95.0,
                                            ],
                                        60 =>
                                            [
                                                0 => 1353343020000,
                                                1 => 53.0,
                                            ],
                                        61 =>
                                            [
                                                0 => 1353429420000,
                                                1 => 85.0,
                                            ],
                                        62 =>
                                            [
                                                0 => 1353515820000,
                                                1 => 45.0,
                                            ],
                                        63 =>
                                            [
                                                0 => 1353602220000,
                                                1 => 36.0,
                                            ],
                                        64 =>
                                            [
                                                0 => 1353688620000,
                                                1 => 53.0,
                                            ],
                                        65 =>
                                            [
                                                0 => 1353775020000,
                                                1 => 10.0,
                                            ],
                                        66 =>
                                            [
                                                0 => 1353861420000,
                                                1 => 56.0,
                                            ],
                                        67 =>
                                            [
                                                0 => 1353947820000,
                                                1 => 80.0,
                                            ],
                                        68 =>
                                            [
                                                0 => 1354034220000,
                                                1 => 70.0,
                                            ],
                                        69 =>
                                            [
                                                0 => 1354120620000,
                                                1 => 51.0,
                                            ],
                                        70 =>
                                            [
                                                0 => 1354207020000,
                                                1 => 17.0,
                                            ],
                                        71 =>
                                            [
                                                0 => 1354293420000,
                                                1 => 21.0,
                                            ],
                                        72 =>
                                            [
                                                0 => 1354379820000,
                                                1 => 92.0,
                                            ],
                                        73 =>
                                            [
                                                0 => 1354466220000,
                                                1 => 32.0,
                                            ],
                                        74 =>
                                            [
                                                0 => 1354552620000,
                                                1 => 46.0,
                                            ],
                                        75 =>
                                            [
                                                0 => 1354639020000,
                                                1 => 71.0,
                                            ],
                                        76 =>
                                            [
                                                0 => 1354725420000,
                                                1 => 66.0,
                                            ],
                                        77 =>
                                            [
                                                0 => 1354811820000,
                                                1 => 81.0,
                                            ],
                                        78 =>
                                            [
                                                0 => 1354898220000,
                                                1 => 30.0,
                                            ],
                                        79 =>
                                            [
                                                0 => 1354984620000,
                                                1 => 41.0,
                                            ],
                                        80 =>
                                            [
                                                0 => 1355071020000,
                                                1 => 83.0,
                                            ],
                                        81 =>
                                            [
                                                0 => 1355157420000,
                                                1 => 19.0,
                                            ],
                                        82 =>
                                            [
                                                0 => 1355243820000,
                                                1 => 0.0,
                                            ],
                                        83 =>
                                            [
                                                0 => 1355330220000,
                                                1 => 1.0,
                                            ],
                                        84 =>
                                            [
                                                0 => 1355416620000,
                                                1 => 45.0,
                                            ],
                                        85 =>
                                            [
                                                0 => 1355503020000,
                                                1 => 70.0,
                                            ],
                                        86 =>
                                            [
                                                0 => 1355589420000,
                                                1 => 54.0,
                                            ],
                                        87 =>
                                            [
                                                0 => 1355675820000,
                                                1 => 27.0,
                                            ],
                                        88 =>
                                            [
                                                0 => 1355762220000,
                                                1 => 66.0,
                                            ],
                                        89 =>
                                            [
                                                0 => 1355848620000,
                                                1 => 91.0,
                                            ],
                                        90 =>
                                            [
                                                0 => 1355935020000,
                                                1 => 17.0,
                                            ],
                                        91 =>
                                            [
                                                0 => 1356021420000,
                                                1 => 83.0,
                                            ],
                                        92 =>
                                            [
                                                0 => 1356107820000,
                                                1 => 12.0,
                                            ],
                                        93 =>
                                            [
                                                0 => 1356194220000,
                                                1 => 60.0,
                                            ],
                                        94 =>
                                            [
                                                0 => 1356280620000,
                                                1 => 11.0,
                                            ],
                                        95 =>
                                            [
                                                0 => 1356367020000,
                                                1 => 30.0,
                                            ],
                                        96 =>
                                            [
                                                0 => 1356453420000,
                                                1 => 50.0,
                                            ],
                                        97 =>
                                            [
                                                0 => 1356539820000,
                                                1 => 85.0,
                                            ],
                                        98 =>
                                            [
                                                0 => 1356626220000,
                                                1 => 51.0,
                                            ],
                                    ],
                                'id' => 'outcome',
                                'name' => 'EffectVariableName (%)',
                                'showInLegend' => true,
                                'tooltip' =>
                                    [
                                        'valueSuffix' => '%',
                                    ],
                                'yAxis' => 1,
                            ],
                    ],
                'title' =>
                    [
                        'text' => 'CauseVariableName Intake & EffectVariableName Over Time',
                    ],
                'useHighStocks' => false,
                'xAxis' =>
                    [
                        'type' => 'datetime',
                        'dateTimeLabelFormats' =>
                            [
                                'day' => '%m/%e/%Y',
                                'week' => '%m/%e/%Y',
                            ],
                    ],
                'yAxis' =>
                    [
                        0 =>
                            [
                                'lineWidth' => 1,
                                'title' =>
                                    [
                                        'text' => 'SUM CauseVariableName intake (mg) ',
                                    ],
                            ],
                        1 =>
                            [
                                'lineWidth' => 1,
                                'opposite' => true,
                                'title' =>
                                    [
                                        'text' => 'MEAN EffectVariableName (%)',
                                    ],
                            ],
                    ],
                'exporting' =>
                    [
                        'filename' => 'causevariablename-intake-effectvariablename-over-time',
                        'showTable' => false,
                    ],
                'card' => NULL,
                'sortingScore' => NULL,
                'id' => 'causevariablename-intake-effectvariablename-over-time',
        ]);
        foreach($config as $key => $value){
			if(!is_string($value)){
				$config->$key = (object) $value;
			} else {
				$config->$key = $value;
			}
        }
        return $config;
    }


}
