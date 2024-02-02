<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
class BaseLabels extends HighchartOption {
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseLabels.formatter
	 */
	public $formatter;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLabels.rotation
	 */
	public $rotation;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseLabels.align
	 */
	public $align;
	/**
	 * @var BaseStyle
	 * @link https://api.highcharts.com/highcharts/style
	 */
	public $style;
	/**
	 * @var BaseItems[]
	 * @link https://api.highcharts.com/highcharts/items
	 */
	public $items;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLabels.x
	 */
	public $x;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLabels.y
	 */
	public $y;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLabels.step
	 */
	public $step;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseLabels.distance
	 */
	public $distance;
	public function __construct(){
		parent::__construct();
		//$this->style = new BaseStyle(); Don't set this or it json encodes to empty array and breaks charts
		$this->items = [];
	}
	/**
	 * @return BaseStyle
	 */
	public function getStyle(): BaseStyle{
		if(!$this->style){$this->style = new BaseStyle();}
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
}
