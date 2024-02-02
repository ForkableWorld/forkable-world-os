<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
use App\Charts\QMHighcharts\Options\BaseSeries;
use App\Charts\SankeyQMChart;
class SankeyHighchart extends HighchartConfig {
	const TYPE = 'sankey';
	protected $canExport = false; // TODO: figure out how to export
	public function __construct(QMChart $QMChart = null){
		parent::__construct($QMChart);
		if($QMChart){
			$this->setTitle($QMChart->getTitleAttribute());
			$this->setSubtitle($QMChart->getSubtitleAttribute());
		}
		unset($this->xAxis);
		unset($this->yAxis);
		// We need plotOptions for Ionic charts
		// $this->unsetPlotOptions();
		$this->setDarkTheme();
		$this->setColors(HighchartConfig::COLORS_FOR_DARK_BACKGROUND_EXCLUDING_WHITE);
		$this->chart->type = self::TYPE;
	}
	public function prepareForRender(): void{
		$this->unsetPlotOptions();
	}
	/**
	 * @param NodeSeries|BaseSeries $series
	 */
	public function addSeriesAndSetColor(BaseSeries $series): void{
		$series->type = SankeyQMChart::SANKEY;
		$this->setDivHeight($series->getHeight());
		parent::addSeries($series);
	}
}
