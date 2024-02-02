<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseChart;
use App\Charts\QMHighcharts\Options\BaseEvents;
use App\Types\QMStr;
class Chart extends BaseChart {
	const ZOOM_XY = 'xy';
	const ZOOM_X = 'x';
	public $alignTicks;               // true
	public $animation;                // undefined
	public $backgroundColor;          // #ffffff
	public $borderColor;              // #335cad
	public $borderRadius;             // 0
	public $borderWidth;              // 0
	public $className;                // undefined
	public $colorCount;               // 10
	public $defaultSeriesType;        // line
	public $displayErrors;            // true
	public $events;                   // {...}
	public $height;                   // null
	public $ignoreHiddenSeries;       // true
	public $inverted;                 // false
	public $margin;                   // undefined
	public $marginBottom;             // undefined
	public $marginLeft;               // undefined
	public $marginRight;              // undefined
	public $marginTop;                // undefined
	public $numberFormatter;          // undefined
	public $options3d;                // {...}
	public $panKey;                   // undefined
	public $parallelCoordinates;      // false
	public $pinchType;                // undefined
	public $plotBackgroundColor;      // undefined
	public $plotBackgroundImage;      // undefined
	public $plotBorderColor;          // #cccccc
	public $plotBorderWidth;          // 0
	public $plotShadow;               // false
	public $polar;                    // false
	public $reflow;                   // true
	public $renderTo;                 // undefined
	public $selectionMarkerFill;      // rgba(51,92,173,0.25)
	public $shadow;                   // false
	public $showAxes;                 // undefined
	public $spacing;                  // [10, 10, 15, 10]
	public $spacingBottom;            // 15
	public $spacingLeft;              // 10
	public $spacingRight;             // 10
	public $spacingTop;               // 10
	public $style;                    // {"fontFamily"; //  "\"Lucida Grande\", \"Lucida Sans Unicode\", Verdana, Arial, Helvetica, sans-serif","fontSize"; // "12px"}
	public $styledMode;               // false
	public $type;                     // line
	public $width;                    // null
	public $zoomKey;                  // undefined
	public $zoomType = self::ZOOM_XY; // undefined
	/**
	 * @param null $obj
	 */
	public function __construct($obj = null){
		if(!$obj){return;}
		parent::__construct($obj);
		unset($this->margin);  // Cuts off labels
		if(!$this->renderTo){
			$this->renderTo = 'container';
		}
		$this->height = null; // Full screen doesn't work if we set height
		//$this->getType();
	}
	/**
	 * @return string
	 */
	public function getType(): string{
		if(!$this->type){
			if(strpos($this->renderTo, 'flow-chart') !== false){
				$this->type = SankeyHighchart::TYPE;
			}
        }
		if(!$this->type){
			le('No type set', $this);
		}
		return $this->type;
	}
	public function setXYZoom(){
		$this->setZoomType(self::ZOOM_XY);
	}
	/**
	 * @param string $zoomType
	 */
	public function setZoomType(string $zoomType): void{
		$this->zoomType = $zoomType;
	}
	public function setXZoom(){
		$this->setZoomType(self::ZOOM_X);
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
}
