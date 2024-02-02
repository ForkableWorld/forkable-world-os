<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseTooltip extends HighchartOption {
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseTooltip.crosshairs
	 */
	public $crosshairs;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseTooltip.shared
	 */
	public $shared;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseTooltip.valueSuffix
	 */
	public $valueSuffix;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseTooltip.formatter
	 */
	public $formatter;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseTooltip.headerFormat
	 */
	public $headerFormat;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseTooltip.pointFormat
	 */
	public $pointFormat;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseTooltip.enabled
	 */
	public $enabled;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseTooltip.followPointer
	 */
	public $followPointer;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseTooltip.valueDecimals
	 */
	public $valueDecimals;
	/**
	 * @var BaseStyle
	 * @link https://api.highcharts.com/highcharts/style
	 */
	public $style;
	public $useHtml; // https://api.highcharts.com/highcharts/tooltip.useHTML
	/**
	 * @var int
	 */
	public $hideDelay; // https://api.highcharts.com/highcharts/tooltip.hideDelay
	public function __construct(){
		parent::__construct();
		//$this->style = new BaseStyle(); Don't set this or it json encodes to empty array and breaks charts
	}
	/**
	 * @return BaseStyle
	 */
	public function getStyle(): BaseStyle{
		if(!$this->style){
			$this->style = new BaseStyle();
		}
		return $this->style;
	}
	public function enableClickingLinks(): void{
		$this->getStyle()->pointerEvents = 'auto';
	}
}
