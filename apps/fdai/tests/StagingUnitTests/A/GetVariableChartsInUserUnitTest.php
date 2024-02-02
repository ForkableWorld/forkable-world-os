<?php /** @noinspection PhpDocMissingThrowsInspection */
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpUnusedLocalVariableInspection */
namespace Tests\StagingUnitTests\A;
use App\Charts\UserVariableCharts\UserVariableChartGroup;
use App\Units\DegreesCelsiusUnit;
use App\Units\DegreesFahrenheitUnit;
use App\Variables\QMUserVariable;
use App\Slim\Model\Measurement\QMMeasurement;
use Tests\SlimStagingTestCase;
use Tests\Traits\TestsCharts;
class GetVariableChartsInUserUnitTest extends SlimStagingTestCase {
	use TestsCharts;
    public function testGetVariableChartsInUserUnitTest(): void{
        $uv = QMUserVariable::getByNameOrId(230, "Outdoor Temperature");
        $this->assertNotNull($uv->numberOfRawMeasurements);
        $this->assertNotNull($uv->numberOfMeasurements);
        $cv = $uv->getCommonVariable();
        $this->assertNotNull($cv->numberOfRawMeasurements);
        $this->assertNotNull($cv->numberOfMeasurements);
        $this->assertEquals(DegreesCelsiusUnit::NAME, $cv->getUnit()->name);
        $measurements = $uv->getValidDailyMeasurementsWithTags();
        $m = QMMeasurement::getFirst($measurements);
        $this->assertEquals(DegreesCelsiusUnit::NAME, $m->getUnit()->name);
        $charts = $uv->getChartGroup();
        $this->compareChartGroup($charts);
        $this->assertEquals(DegreesFahrenheitUnit::NAME, $uv->getUnit()->name);
        $this->checkLineChart($charts);
        $this->checkMonthCharts($charts);
        $this->checkWeekChart($charts);
        $this->checkDistributionChart($charts);
    }
    /**
     * @param UserVariableChartGroup $charts
     */
    protected function checkLineChart($charts): void{
        $overTime = $charts->lineChartWithSmoothing;
        $config = $overTime->getHighchartConfig();
        $series = $config->getSeries();
        $data = $series[0]->data;
        $values = array_column($data, 1);
        $max = max($values);
        $this->assertGreaterThan(90, $max, "Data should be in user unit F not C, but max temp is $max");
        $this->assertEquals("Outdoor Temperature", $series[0]->name, "Should contain user unit F not C");
		$this->assertArrayEquals([
			0 => 'Outdoor',
			1 => 'Outdoor Temperature',
			2 => 'Outdoor Temperature Over Time',
			3 => 'Outdoor Temperature Over Time Spline Chart',
			4 => 'Outdoor Temperature Spline Chart',], $overTime->getKeyWords());
    }
    /**
     * @param UserVariableChartGroup $charts
     */
    protected function checkMonthCharts($charts): void{
        $chart = $charts->monthlyColumnChart;
        $config = $chart->getHighchartConfig();
        $values = $config->series[0]->data;
        $max = max($values);
        $this->assertGreaterThan(80, $max, "Data should be in user unit F not C, but max temp is $max");
        $this->assertGreaterThan(80, $config->yAxis->max, "Data should be in user unit F not C, but max temp is $max");
        $seriesName = $config->yAxis->title->text;
        $this->assertEquals("Daily Average (F)", $seriesName, "Should contain user unit F not C");
	    $this->assertArrayEquals(array (
		    0 => 'Outdoor',
		    1 => 'Outdoor Temperature',
		    2 => 'Average Outdoor Temperature by Month',
		    3 => 'Average Outdoor Temperature by Month Column Chart',
		    4 => 'Outdoor Temperature Column Chart',
	    ), $chart->getKeyWords());
    }
    /**
     * @param UserVariableChartGroup $charts
     */
    protected function checkDistributionChart(UserVariableChartGroup $charts): void{
        $overTime = $charts->distributionColumnChart;
        $config = $overTime->getHighchartConfig();
        $series = $config->xAxis->categories;
        $max = max($series);
        $this->assertGreaterThan(90, $max, "Data should be in user unit F not C, but max temp is $max");
        $seriesName = $config->xAxis->title->text;
        $this->assertEquals("Daily Values (F)", $seriesName, "Should contain user unit F not C");
	    $this->assertArrayEquals(array (
		    0 => 'Outdoor',
		    1 => 'Outdoor Temperature',
		    2 => 'Daily Outdoor Temperature Distribution',
		    3 => 'Daily Outdoor Temperature Distribution Column Chart',
		    4 => 'Outdoor Temperature Column Chart',
	    ), $overTime->getKeyWords());
    }
    /**
     * @param UserVariableChartGroup $charts
     */
    protected function checkWeekChart($charts): void{
        $overTime = $charts->weekdayColumnChart;
        $config = $overTime->getHighchartConfig();
        $values = $config->series[0]->data;
        $max = max($values);
        $this->assertGreaterThan(60, $max, "Data should be in user unit F not C, but max temp is $max");
        $this->assertGreaterThan(60, $config->yAxis->max, "Data should be in user unit F not C, but max temp is $max");
        $seriesName = $config->yAxis->title->text;
        $this->assertEquals("Average (F)", $seriesName, "Should contain user unit F not C");
	    $this->assertArrayEquals(array (
		    0 => 'Outdoor',
		    1 => 'Outdoor Temperature',
		    2 => 'Average Outdoor Temperature by Day of Week',
		    3 => 'Average Outdoor Temperature by Day of Week Column Chart',
		    4 => 'Outdoor Temperature Column Chart',
	    ), $overTime->getKeyWords());
    }
}
