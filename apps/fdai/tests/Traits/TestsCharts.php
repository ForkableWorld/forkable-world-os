<?php
namespace Tests\Traits;
use App\Charts\QMChart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Exceptions\DiffException;
use App\Exceptions\QMFileNotFoundException;
use App\Files\HtmlFile;
use App\Logging\ConsoleLog;
use PHPUnit\Framework\Assert;
trait TestsCharts {
	/**
	 * @param $chartGroup
	 * @param string|null $key
	 * @throws DiffException
	 * @throws QMFileNotFoundException
	 */
	public static function compareChartGroup($chartGroup, string $key = null){
		foreach($chartGroup as $type => $chart){
			if(is_object($chart)){
				if($key){
					$type = "$key-$type";
				}
				static::compareChart($type, $chart);
			}
		}
	}
	/**
	 * @param string  $key
	 * @param QMChart|object $chart
	 * @throws QMFileNotFoundException
	 * @throws DiffException
	 */
	public static function compareChart(string $key, $chart){
		ConsoleLog::info(__FUNCTION__.": $key");
		$highchart = $chart->highchartConfig;
		if(!$highchart){le("No highchart config for $key");}
		$highchart = HighchartConfig::instantiateIfNecessary($highchart);
		$html = $highchart->getHtmlPage();
		Assert::assertStringContainsString("<head", $html, "chart html for $key");
		HtmlFile::assertSameHtml($key, $html);
	}
}
