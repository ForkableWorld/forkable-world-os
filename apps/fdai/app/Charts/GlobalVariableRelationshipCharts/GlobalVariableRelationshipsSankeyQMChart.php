<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\GlobalVariableRelationshipCharts;
use App\Charts\NetworkGraphQMChart;
use App\Charts\SankeyQMChart;
use App\Models\UserVariableRelationship;
use App\Traits\HasCorrelationNodeSeries;
use App\Traits\HasOutcomesAndPredictors;
use App\Types\QMStr;
use App\Variables\QMVariable;
use Illuminate\Support\Collection;
class GlobalVariableRelationshipsSankeyQMChart extends SankeyQMChart {
	use HasCorrelationNodeSeries;
	/**
	 * @param null $variable
	 * @param string|null $title
	 * @param int|null $limit
	 */
	public function __construct($variable = null, string $title = null, int $limit = NetworkGraphQMChart::MAX_ITEMS){
		$this->limit = $limit;
		parent::__construct(null, $variable, $title);
	}
	/**
	 * @return QMVariable|HasOutcomesAndPredictors
	 */
	public function getQMVariable(): QMVariable{
		return $this->sourceObject->getDBModel();
	}
	public function getTitleAttribute(): string{
		$variable = $this->getQMVariable();
		$title = $variable->getCorrelationsChartTitle() . " Flow Chart";
		return $this->title = $title;
	}
	public function getId(): string{
		return $this->id = QMStr::slugify($this->getTitleAttribute());
	}
	public function getSubtitleAttribute(): string{
		$variable = $this->getQMVariable();
		$correlations = $this->getOutcomesOrPredictors();
		if(!$correlations->count()){
			//            throw new NotEnoughDataException($variable, "Not Enough Data",
			//                "No Correlational Analyses Available to Create Chart");
			return $this->explanation = $variable->getCorrelationDataRequirementAndCurrentDataQuantityString();
		} elseif($variable->isOutcome()){
			return $this->explanation = $variable->getCorrelationsChartSubTitle() .
				"The percent value indicates the typical change in $variable->name from baseline following above " .
				"average measurements for the predictor on the left.";
		} else{
			return $this->explanation = $variable->getCorrelationsChartSubTitle() .
				"The percent value indicates the typical change from baseline in the outcome on the right following " .
				"above average measurements for $variable->name.";
		}
	}
	/**
	 * @return Collection|UserVariableRelationship[]
	 */
	protected function getOutcomesOrPredictors(): Collection{
		$variable = $this->getQMVariable();
		$correlations = $variable->getPublicOutcomesOrPredictors($this->limit);
		return $correlations;
	}
}
