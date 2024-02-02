<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMHighcharts\Options\BaseTooltip;
class Tooltip extends BaseTooltip {
	public $valueDecimals;
	public $valueSuffix;
	/**
	 * Tooltip constructor.
	 * @param string|null $unitName
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(string $unitName = null){
		if($unitName){
			$this->setValueSuffix($unitName);
		}
		$this->setFormatter("");
	}
	public function setFormatter(string $jsFunction): void{
		$this->formatter = new HighchartJsExpr("function() {
            var series = this.series || this.points[0].series || this.points[0].chart.series;
            var tooltips = series.options.tooltips;
            if(tooltips){
                var x = this.x || this.point.x
                var tooltip = tooltips[x] || null;
                if(tooltip){
                    //console.warn(this.point)
                    //debugger
                    return tooltip
                }
            }
            $jsFunction
        }");
	}
	/**
	 * @param string $valueSuffix
	 */
	public function setValueSuffix(string $valueSuffix): void{
		$this->valueSuffix = " " . trim($valueSuffix);
	}
	/**
	 * @return string
	 */
	public function getValueSuffix(): string{
		return $this->valueSuffix;
	}
}
