<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BasePlotLines extends HighchartOption {
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/basePlotLines.value
	 */
	public $value;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/basePlotLines.width
	 */
	public $width;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/basePlotLines.color
	 */
	public $color;
	/**
	 * @var BaseLabel
	 * @link https://api.highcharts.com/highcharts/label
	 */
	public $label;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/basePlotLines.dashStyle
	 */
	public $dashStyle;
	public function __construct(){
		parent::__construct();
		$this->label = new BaseLabel();
	}
}
