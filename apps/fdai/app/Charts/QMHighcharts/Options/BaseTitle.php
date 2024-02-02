<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseTitle extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseTitle.text
	 */
	public $text;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseTitle.enabled
	 */
	public $enabled;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseTitle.align
	 */
	public $align;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseTitle.x
	 */
	public $x;
	/**
	 * @var BaseStyle
	 * @link https://api.highcharts.com/highcharts/style
	 */
	public $style;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseTitle.y
	 */
	public $y;
	/**
	 * @return BaseStyle
	 */
	public function getStyle(): BaseStyle{
		// We can set color so it matches when we have multiple series
		// le("Text color should be set in theme!");
		if(!isset($this->style)){
			$this->style = new BaseStyle();
		}
		if(!$this->style instanceof BaseStyle){
			$this->style = BaseStyle::instantiateIfNecessary($this->style);
		}
		return $this->style;
	}
	public function setColor(string $color){
		// We can set color so it matches when we have multiple series
		// le("Text color should be set in theme!");
		$this->getStyle()->color = $color;
	}
	/**
	 * @param string|null $text
	 */
	public function setText(?string $text): void{
		$this->text = $text;
	}
}
