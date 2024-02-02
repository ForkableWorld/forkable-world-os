<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\CorrelationCharts;
use App\Charts\NetworkGraphQMChart;
use App\Traits\HasCorrelationNodeSeries;
use App\Variables\QMVariable;
class CorrelationsNetworkGraphQMChart extends NetworkGraphQMChart {
	use HasCorrelationNodeSeries;
	/**
	 * @param QMVariable|null $variable
	 * @param string|null $title
	 * @param int $limit
	 */
	public function __construct($variable = null, string $title = null, int $limit = self::MAX_ITEMS){
		$this->limit = $limit;
		parent::__construct(null, $variable, $title);
	}
	/**
	 * @return QMVariable
	 */
	public function getQMVariable(): QMVariable{
		return $this->sourceObject->getQMVariable();
	}
	public function getTitleAttribute(): string{
		$variable = $this->getSourceObject();
		$t = $variable->getCorrelationsChartTitle() . " Network Graph";
		return $this->title = $t;
	}
	public function getSubtitleAttribute(): string{
		$variable = $this->getQMVariable();
		$limit = $this->limit;
		$correlations = $variable->getOutcomesOrPredictors($limit);
		if(!$correlations->count()){
			//            throw new NotEnoughDataException($variable, "Not Enough Data",
			//                "No Correlational Analyses Available to Create Chart");
			return $variable->getCorrelationDataRequirementAndCurrentDataQuantityString();
		} elseif($variable->isOutcome()){
			return $variable->getCorrelationsChartSubTitle() .
				" The size is proportional to the change in $variable->name following above average measurements for the predictor.";
		} else{
			return $variable->getCorrelationsChartSubTitle() .
				"The size is proportional to the change from baseline in the outcome following above average measurements for $variable->name.";
		}
	}
}
