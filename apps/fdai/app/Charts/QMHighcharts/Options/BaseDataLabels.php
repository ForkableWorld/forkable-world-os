<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Charts\QMHighcharts\Options;
use App\Charts\QMHighcharts\HighchartJsExpr;
use App\Charts\QMHighcharts\HighchartOption;
class BaseDataLabels extends HighchartOption {
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.enabled
	 */
	public $enabled;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.formatter
	 */
	public $formatter;
	/**
	 * @var object
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.color
	 */
	public $color;
	/**
	 * @var BaseStyle
	 * @link https://api.highcharts.com/highcharts/style
	 */
	public $style;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.rotation
	 */
	public $rotation;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.align
	 */
	public $align;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.x
	 */
	public $x;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.y
	 */
	public $y;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.format
	 */
	public $format;
	/**
	 * @var bool
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.softConnector
	 */
	public $softConnector;
	/**
	 * @var BaseBackgroundColor
	 * @link https://api.highcharts.com/highcharts/backgroundColor
	 */
	public $backgroundColor;
	/**
	 * @var int
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.distance
	 */
	public $distance;
	/**
	 * @var string
	 * @link https://api.highcharts.com/highcharts/baseDataLabels.connectorColor
	 */
	public $connectorColor;
	public function __construct(){
		parent::__construct();
		//$this->style = new BaseStyle(); Don't set this or it json encodes to empty array and breaks charts
		$this->backgroundColor = new BaseBackgroundColor();
		$this->setFormatter("");
	}
	public function setFormatter(string $jsFunction): void{
		$this->formatter = new HighchartJsExpr("function() {
            var label = this.series.options.labels[this.point.x] || null;
            if(label){
                //console.warn(this.point)
                //debugger
                return label
            }
            $jsFunction
        }");
	}
}
