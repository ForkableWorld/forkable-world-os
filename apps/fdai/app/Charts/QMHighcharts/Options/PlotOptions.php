<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class PlotOptions extends HighchartOption {
	/**
	 * @var BaseAreaspline
	 * @link https://api.highcharts.com/highcharts/areaspline
	 */
	public $areaspline;
	/**
	 * @var BaseArea
	 * @link https://api.highcharts.com/highcharts/area
	 */
	public $area;
	/**
	 * @var BaseSeries
	 * @link https://api.highcharts.com/highcharts/series
	 */
	public $series;
	/**
	 * @var BaseBar
	 * @link https://api.highcharts.com/highcharts/bar
	 */
	public $bar;
	/**
	 * @var BaseColumn
	 * @link https://api.highcharts.com/highcharts/column
	 */
	public $column;
	/**
	 * @var BaseDataLabels
	 * @link https://api.highcharts.com/highcharts/dataLabels
	 */
	public $dataLabels;
	/**
	 * @var BaseSpline
	 * @link https://api.highcharts.com/highcharts/spline
	 */
	public $spline;
	/**
	 * @var BaseLine
	 * @link https://api.highcharts.com/highcharts/line
	 */
	public $line;
	/**
	 * @var BaseGauge
	 * @link https://api.highcharts.com/highcharts/gauge
	 */
	public $gauge;
	/**
	 * @var BasePie
	 * @link https://api.highcharts.com/highcharts/pie
	 */
	public $pie;
	/**
	 * @var BaseScatter
	 * @link https://api.highcharts.com/highcharts/scatter
	 */
	public $scatter;
	public function __construct(){
		parent::__construct();
		//		$this->areaspline = new BaseAreaspline();
		//		$this->area = new BaseArea();
		//		$this->series = new BaseSeries();
		//		$this->bar = new BaseBar();
		//		$this->column = new BaseColumn();
		//		$this->dataLabels = new BaseDataLabels();
		//		$this->spline = new BaseSpline();
		//		$this->line = new BaseLine();
		//		$this->gauge = new BaseGauge();
		//		$this->pie = new BasePie();
		//		$this->scatter = new BaseScatter();
	}
	/**
	 * @return BaseSeries
	 */
	public function getSeries(): BaseSeries{
		if(!$this->series){
			$this->series = new BaseSeries();
		}
		return $this->series;
	}
}
