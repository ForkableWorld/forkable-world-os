<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
use App\Charts\QMHighcharts\Options\BaseScatter;
use App\Traits\HasCorrelationCoefficient;
class ScatterHighchartConfig extends HighchartConfig {
	/**
	 * ScatterHighchartConfig constructor.
	 * @param HasCorrelationCoefficient|null $correlation
	 * @param QMChart|null $QMChart
	 */
	public function __construct($correlation = null, QMChart $QMChart = null){
		parent::__construct($QMChart);
		$this->chart->type = 'scatter';
		$this->getChart()->setXYZoom();
		if(!$correlation){
			return;
		}
		$this->getPlotOptions()->scatter = new ScatterPlotOptions();
	}
	public function setTooltipFormatter(string $jsFunction): void{
		parent::setTooltipFormatter($jsFunction);
		$this->getScatterPlotOptions()->getTooltip()->setFormatter($jsFunction);
	}
	public function getScatterPlotOptions(): BaseScatter{
		return $this->getPlotOptions()->scatter;
	}
	public function getHtml(bool $includeJS = true): string{
		return parent::getHtml($includeJS);
	}
}
