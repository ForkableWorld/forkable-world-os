<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseStyle extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.position
	 */
	public $position;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.right
	 */
	public $right;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.bottom
	 */
	public $bottom;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.fontWeight
	 */
	public $fontWeight;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.font
	 */
	public $font;
	/**
	 * @var string
	 */
	public $color;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.left
	 */
	public $left;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.top
	 */
	public $top;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.margin
	 */
	public $margin;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.fontSize
	 */
	public $fontSize;
	// Doesn't seem to affect anything public $fontFamily = "poppins, sans-serif";
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.lineHeight
	 */
	public $lineHeight;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.textShadow
	 */
	public $textShadow;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseStyle.width
	 */
	public $width;
	/**
	 * @var false
	 */
	public $textOutline;
	public $pointerEvents;
	public function __construct(){
		parent::__construct();
	}
	/**
	 * @return string
	 */
	public function getColor(): string{
		return $this->color;
	}
}
