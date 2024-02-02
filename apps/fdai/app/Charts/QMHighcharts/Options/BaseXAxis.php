<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartOption;
use App\Charts\QMHighcharts\Options\BaseCategories;
class BaseXAxis extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseXAxis.type
	 */
	public $type;
	public $categories;
	/**
	 * @var BasePlotBands
	 * @link https://api.highcharts.com/highcharts/plotBands
	 */
	public $plotBands;
	/**
	 * @var BaseLabels
	 * @link https://api.highcharts.com/highcharts/labels
	 */
	public $labels;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseXAxis.tickmarkPlacement
	 */
	public $tickmarkPlacement;
	/**
	 * @var BaseTitle
	 * @link https://api.highcharts.com/highcharts/title
	 */
	public $title;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseXAxis.reversed
	 */
	public $reversed;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseXAxis.min
	 */
	public $min;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseXAxis.max
	 */
	public $max;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseXAxis.minPadding
	 */
	public $minPadding;
	/**
	 * @var float
	 * @link https://api.highcharts.com/highcharts/baseXAxis.maxPadding
	 */
	public $maxPadding;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.maxZoom
	 */
	public $maxZoom;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.tickPixelInterval
	 */
	public $tickPixelInterval;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.tickInterval
	 */
	public $tickInterval;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.tickWidth
	 */
	public $tickWidth;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.gridLineWidth
	 */
	public $gridLineWidth;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseXAxis.showLastLabel
	 */
	public $showLastLabel;
	/**
	 * @var BaseDateTimeLabelFormats
	 * @link https://api.highcharts.com/highcharts/dateTimeLabelFormats
	 */
	public $dateTimeLabelFormats;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.lineWidth
	 */
	public $lineWidth;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.startOnTick
	 */
	public $startOnTick;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.endOnTick
	 */
	public $endOnTick;
	/**
	 * @var BaseEvents
	 * @link https://api.highcharts.com/highcharts/events
	 */
	public $events;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.minRange
	 */
	public $minRange;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseXAxis.gapGridLineWidth
	 */
	public $gapGridLineWidth;
	public function __construct(){
		parent::__construct();
		// This stuff breaks scatterplot export. Only add when needed
		//$this->categories = [];
		//$this->plotBands = new BasePlotBands();
		//$this->labels = new BaseLabels();
		$this->title = new BaseTitle();
		//$this->dateTimeLabelFormats = new BaseDateTimeLabelFormats();
		//$this->events = new BaseEvents();
	}
	public function getCategories(): array{
		if(is_array($this->categories)){
			foreach($this->categories as $i => $val){
				$this->categories[$i] = (string)$val;
			}
		}
		return $this->categories;
	}
	/**
	 * @param array $categories
	 */
	public function setCategories(array $categories): void{
		foreach($categories as $i => $val){
			$categories[$i] = (string)$val;
		}
		$this->categories = $categories;
	}
}
