<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartJsExpr;
use App\Charts\QMHighcharts\HighchartOption;
use App\Utils\Stats;
class BaseSeries extends HighchartOption {
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.name
	 */
	public $name;
	/**
	 * @var array
	 * @link https://api.highcharts.com/highcharts/baseSeries.data
	 */
	public $data = [];
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.zIndex
	 */
	public $zIndex;
	/**
	 * @var BaseMarker
	 * @link https://api.highcharts.com/highcharts/marker
	 */
	public $marker;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.stacking
	 */
	public $stacking;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.color
	 */
	public $color;
	/**
	 * @var BaseDataLabels
	 * @link https://api.highcharts.com/highcharts/dataLabels
	 */
	public $dataLabels;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.stack
	 */
	public $stack;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.type
	 */
	public $type;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.yAxis
	 */
	public $yAxis;
	/**
	 * @var BaseStates
	 * @link https://api.highcharts.com/highcharts/states
	 */
	public $states;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseSeries.enableMouseTracking
	 */
	public $enableMouseTracking;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.lineWidth
	 */
	public $lineWidth;
	/**
	 * @var BasePoint
	 * @link https://api.highcharts.com/highcharts/point
	 */
	public $point;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.cursor
	 */
	public $cursor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.pointStart
	 */
	public $pointStart;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.pointInterval
	 */
	public $pointInterval;
	/**
	 * @var BaseTooltip
	 * @link https://api.highcharts.com/highcharts/tooltip
	 */
	public $tooltip;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseSeries.animation
	 */
	public $animation;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.neckWidth
	 */
	public $neckWidth;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.neckHeight
	 */
	public $neckHeight;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.pointPlacement
	 */
	public $pointPlacement;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseSeries.upColor
	 */
	public $upColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.pointPadding
	 */
	public $pointPadding;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseSeries.shadow
	 */
	public $shadow;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.groupPadding
	 */
	public $groupPadding;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.size
	 */
	public $size;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.joinBy
	 */
	public $joinBy;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseSeries.mapData
	 */
	public $mapData;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.threshold
	 */
	public $threshold;
	/**
	 * @var BaseFillColor
	 * @link https://api.highcharts.com/highcharts/fillColor
	 */
	public $fillColor;
	/**
	 * @var BaseDataGrouping
	 * @link https://api.highcharts.com/highcharts/dataGrouping
	 */
	public $dataGrouping;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.step
	 */
	public $step;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.id
	 */
	public $id;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseSeries.compare
	 */
	public $compare;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseSeries.gapSize
	 */
	public $gapSize;
	/**
	 * @var BaseEvents
	 * @link https://api.highcharts.com/highcharts/events
	 */
	public $events;
	public $tooltips;
	public $labels;
	/**
	 * @var bool
	 */
	public $visible = true;
	public function __construct(){
		parent::__construct();
		$this->marker = new BaseMarker();
		$this->dataLabels = new BaseDataLabels();
		$this->states = new BaseStates();
		$this->point = new BasePoint();
		$this->fillColor = new BaseFillColor();
		$this->dataGrouping = new BaseDataGrouping();
	}
	public function addClickEvent(string $js){
		$this->getEvents()->click = new HighchartJsExpr($js);
	}
	/**
	 * @return BaseEvents
	 */
	public function getEvents(): BaseEvents{
		if(!$this->events){
			$this->events = new BaseEvents();
		}
		return $this->events;
	}
	/**
	 * @return array
	 */
	public function getData(): array{
		return $this->data;
	}
	public function getAverage(): float{
		return Stats::average($this->getValues());
	}
	public function getValues(): array{
		$values = [];
		foreach($this->data as $i => $datum){
			if(is_array($datum)){
				$y = $datum['y'];
			} else{
				$y = $datum;
			}
			$values[] = $y;
		}
		return $values;
	}
	public function addTooltip(int $x, string $tooltip){
		$this->tooltips[$x] = $tooltip;
	}
	public function addLabel(int $x, string $label){
		$this->labels[$x] = $label;
	}
	public function setVisibility(bool $val){
		$this->visible = $val;
	}
	/**
	 * @return BaseMarker
	 */
	public function getMarker(): BaseMarker{
		if(!$this->marker){
			$this->marker = new BaseMarker();
		}
		return $this->marker;
	}
}
