<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseSubtitle extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSubtitle.text
	 */
	public $text;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseSubtitle.floating
	 */
	public $floating;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSubtitle.align
	 */
	public $align;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSubtitle.verticalAlign
	 */
	public $verticalAlign;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSubtitle.y
	 */
	public $y;
	/**
	 * @var BaseStyle
	 * @link https://api.highcharts.com/highcharts/style
	 */
	public $style;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSubtitle.x
	 */
	public $x;
	/**
	 * @param string|null $text
	 */
	public function setText(?string $text): void{
		$this->text = $text;
	}
}
