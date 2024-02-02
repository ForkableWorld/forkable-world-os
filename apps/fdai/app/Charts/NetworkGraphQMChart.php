<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\Chart;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\NetworkGraphPlotOptions;
use App\Charts\QMHighcharts\NetworkGraphTooltip;
use App\Charts\QMHighcharts\NodeSeries;
use App\Charts\QMHighcharts\Options\BaseTooltip;
abstract class NetworkGraphQMChart extends NodeQMChart {
	public const MAX_ITEMS = 50; // Any more crashes the browser
	protected $canExport = false;
	public function generateHighchartConfig(): HighchartConfig{
		$conf = new HighchartConfig($this);
		$chart = new Chart();
		$chart->type = self::TYPE_NETWORKGRAPH;
		$conf->chart = $chart;
		$conf->setTitle($this->getTitleAttribute());
		$conf->setSubtitle($this->getSubtitleAttribute());
		$conf->setPlotOptions(new NetworkGraphPlotOptions());
		return $this->highchartConfig = $conf;
	}
	public function addSeries(NodeSeries $series){
		$series->type = self::TYPE_NETWORKGRAPH;
		if(count($series->data) > static::MAX_ITEMS){
			$series->data = array_slice($series->data, 0, static::MAX_ITEMS);
		}
		$this->getHighchartConfig()->setSeries([$series]);
	}
	public function getTooltip(): BaseTooltip{
		return new NetworkGraphTooltip();
	}
}
