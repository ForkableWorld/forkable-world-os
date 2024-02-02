<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
use App\Charts\QMHighcharts\Options\BaseCenter;
class BasePane extends HighchartOption {
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/basePane.startAngle
	 */
	public $startAngle;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/basePane.endAngle
	 */
	public $endAngle;
	/**
	 * @var BaseBackground[]
	 * @link https://api.highcharts.com/highcharts/background
	 */
	public $background;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/basePane.size
	 */
	public $size;
	/**
	 * @var BaseCenter[]
	 * @link https://api.highcharts.com/highcharts/center
	 */
	public $center;
	public function __construct(){
		parent::__construct();
		$this->background = [];
		$this->center = [];
	}
}
