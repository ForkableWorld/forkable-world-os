<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BasePie extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/basePie.shadow
	 */
	public $shadow;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/basePie.allowPointSelect
	 */
	public $allowPointSelect;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/basePie.cursor
	 */
	public $cursor;
	/**
	 * @var BaseDataLabels
	 * @link https://api.highcharts.com/highcharts/dataLabels
	 */
	public $dataLabels;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/basePie.showInLegend
	 */
	public $showInLegend;
	public function __construct(){
		parent::__construct();
		$this->dataLabels = new BaseDataLabels();
	}
}
