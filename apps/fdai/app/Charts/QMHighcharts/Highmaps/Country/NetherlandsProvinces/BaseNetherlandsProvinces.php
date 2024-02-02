<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Highmaps\Country\NetherlandsProvinces;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseColorAxis;
use App\Charts\QMHighcharts\Options\BaseMapNavigation;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\QMHighcharts\Options\BaseTitle;
class BaseNetherlandsProvinces extends HighchartConfig {
	/**
	 * @var BaseChart
	 * @link https://api.highcharts.com/highcharts/chart
	 */
	public $chart;
	/**
	 * @var BaseTitle
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
	/**
	 * @var BaseColorAxis
	 * @link https://api.highcharts.com/highcharts/colorAxis
	 */
	public $colorAxis;
	/**
	 * @var BaseMapNavigation
	 * @link https://api.highcharts.com/highcharts/mapNavigation
	 */
	public $mapNavigation;
	/**
	 * @var BaseSeries[]
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	public function __construct(){
		parent::__construct();
		$this->chart = new BaseChart();
		$this->title = new BaseTitle();
		$this->colorAxis = new BaseColorAxis();
		$this->mapNavigation = new BaseMapNavigation();
		$this->series = [];
	}
	public function demo(): string{
		/** @noinspection PhpIncludeInspection */
		require base_path('vendor/ghunti/highcharts-php/demos/highmaps/country/netherlands_provinces.php');
	}
}
