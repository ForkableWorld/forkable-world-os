<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\NodeSeries;
use App\Charts\QMHighcharts\Options\BaseTooltip;
use App\Charts\QMHighcharts\SankeyHighchart;
use App\Charts\QMHighcharts\SankeyTooltip;
abstract class SankeyQMChart extends NodeQMChart {
	const SANKEY = "sankey";
	protected $canExport = false; // TODO: figure out how to export
	protected $css = ".highcharts-color-0 {
	fill: #666666;
}";
	/**
	 * @return HighchartConfig
	 */
	public function generateHighchartConfig(): HighchartConfig{
		$conf = new SankeyHighchart($this);
		$conf->type = self::SANKEY;
		return $this->setHighchartConfig($conf);
	}
	protected function setTheme(HighchartConfig $config){ // Override this for individual charts if necessary
		$config->setDarkTheme();
		$config->setColors(HighchartConfig::COLORS_FOR_DARK_BACKGROUND_EXCLUDING_WHITE);
		// Avoids rainbow nodes and white which is the same as text
	}
	public function addSeries(NodeSeries $series): void{
		$series->type = self::SANKEY;
		$c = $this->getHighchartConfig();
		$c->setDivHeight($series->getHeight());
		$c->addSeriesAndYAxis($series);
	}
	public function getDynamicHtml(bool $includeJS = true): string{
		return parent::getDynamicHtml($includeJS);
	}
	public function getHtml(): string{
		return parent::getHtml();
	}
	public function getId(): string{
		return parent::getId();
	}
	public function getTooltip(): BaseTooltip{
		return new SankeyTooltip();
	}
}
