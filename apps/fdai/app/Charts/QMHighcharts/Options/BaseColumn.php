<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseColumn extends HighchartOption {
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseColumn.pointPadding
	 */
	public $pointPadding;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseColumn.borderWidth
	 */
	public $borderWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseColumn.cursor
	 */
	public $cursor;
	/**
	 * @var BasePoint
	 * @link https://api.highcharts.com/highcharts/point
	 */
	public $point;
	/**
	 * @var BaseDataLabels
	 * @link https://api.highcharts.com/highcharts/dataLabels
	 */
	public $dataLabels;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseColumn.stacking
	 */
	public $stacking;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseColumn.groupPadding
	 */
	public $groupPadding;
	public function __construct(){
		parent::__construct();
		$this->point = new BasePoint();
		$this->dataLabels = new BaseDataLabels();
	}
}
