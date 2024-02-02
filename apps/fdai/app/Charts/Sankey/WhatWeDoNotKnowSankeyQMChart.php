<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\Sankey;
use App\Charts\QMHighcharts\HighchartConfig;
use App\Charts\QMHighcharts\WhatWeDoNotKnowSeries;
use App\Charts\SankeyQMChart;
use App\Storage\LocalFileCache;
use App\VariableCategories\NutrientsVariableCategory;
class WhatWeDoNotKnowSankeyQMChart extends SankeyQMChart {
	public $explanation = "All the combinations of factors and chronic illness for which we do not know the optimal daily values. ";
	public $id = 'predictor-outcome-sankey-chart';
	public $chartTitle = "Optimal Daily Values We Don't Know";
	public function __construct($chartRow = null, $sourceObject = null, string $title = null){
		parent::__construct($chartRow, $sourceObject, $title);
	}
	public function getHighchartConfig(): HighchartConfig{
		$config = parent::getHighchartConfig();
		return $config;
	}
	public function generateHighchartConfig(): HighchartConfig{
		$config = parent::generateHighchartConfig();
		$this->addSeries(WhatWeDoNotKnowSeries::get());
		return $config;
	}
	public static function getCauses(){
		$cached = LocalFileCache::get(__METHOD__);
		if($cached){
			return $cached;
		}
		$nutrients = NutrientsVariableCategory::getVariableNames();
		//\Cache::remember(__METHOD__, )
	}
}
