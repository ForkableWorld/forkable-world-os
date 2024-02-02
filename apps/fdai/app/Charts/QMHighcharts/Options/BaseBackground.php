<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseBackground extends HighchartOption {
	/**
	 * @var BaseBackgroundColor
	 * @link https://api.highcharts.com/highcharts/backgroundColor
	 */
	public $backgroundColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseBackground.borderWidth
	 */
	public $borderWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseBackground.outerRadius
	 */
	public $outerRadius;
	public function __construct(){
		parent::__construct();
		$this->backgroundColor = new BaseBackgroundColor();
	}
}
