<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts;
use App\Charts\QMHighcharts\DataLabels;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\WhatWeDoNotKnowSeries;
class WhatWeDoNotKnowNetworkGraphChart extends NetworkGraphQMChart {
	public $id = "what-we-do-not-know-network-graph";
	public $chartTitle = "What We Don't Know";
	public $explanation = "All the combinations of factors and chronic illness for which we do not know the optimal daily values. ";
	public function generateHighchartConfig(): HighchartConfig{
		$conf = parent::generateHighchartConfig();
		$series = WhatWeDoNotKnowSeries::get();
		$series->type = "networkgraph";
		$series->dataLabels = new DataLabels();
		$series->dataLabels->enabled = true;
		$this->addSeries($series);
		return $this->highchartConfig = $conf;
	}
}
