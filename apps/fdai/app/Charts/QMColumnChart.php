<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\HighchartConfig;
use App\UI\QMColor;
use App\Variables\QMVariable;
abstract class QMColumnChart extends QMMeasurementsChart {
	protected $labelValueArray;
	protected $seriesName;
	/**
	 * @param QMVariable $v
	 */
	public function __construct($v = null){
		if(!$v){
			return;
		}
		parent::__construct($v);
	}
	/**
	 * @param HighchartConfig $highchartConfig
	 */
	protected function setPositiveAndNegativeColorsIfNecessary(HighchartConfig $highchartConfig): void{
		if($this->sourceObject instanceof QMVariable){
			$variable = $this->getQMVariable();
			$avg = null;
			if(strpos($variable->getVariableName(), "Daily Return") !== false){
				$avg = 0;
			}
			if($variable->valenceIsPositive()){
				$highchartConfig->setPositiveAndNegativeColorsByY(QMColor::HEX_GOOGLE_GREEN, QMColor::HEX_GOOGLE_RED,
					$avg);
			} elseif($variable->valenceIsNegative()){
				$highchartConfig->setPositiveAndNegativeColorsByY(QMColor::HEX_GOOGLE_RED, QMColor::HEX_GOOGLE_GREEN,
					$avg);
			}
		}
	}
}
