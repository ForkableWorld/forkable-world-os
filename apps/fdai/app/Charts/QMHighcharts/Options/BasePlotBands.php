<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BasePlotBands extends HighchartOption {
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/basePlotBands.from
	 */
	public $from;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/basePlotBands.to
	 */
	public $to;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/basePlotBands.color
	 */
	public $color;
	/**
	 * @var BaseLabel
	 * @link https://api.highcharts.com/highcharts/label
	 */
	public $label;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/basePlotBands.innerRadius
	 */
	public $innerRadius;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/basePlotBands.outerRadius
	 */
	public $outerRadius;
	public function __construct(){
		parent::__construct();
		$this->label = new BaseLabel();
	}
}
