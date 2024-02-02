<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Variable;
use App\Variables\QMVariable;
use Illuminate\Support\Collection;
class CorrelationNodeSeries extends NodeSeries {
	/**
	 * @param string $name
	 * @param Variable|QMVariable $variable
	 * @param UserVariableRelationship[]|GlobalVariableRelationship[]|Collection $correlations
	 */
	public function __construct(string $name, $variable, $correlations){
		parent::__construct($name);
		$withoutSelf = CorrelationNodeSeries::sortAndFilterCorrelations($correlations);
		/** @var UserVariableRelationship|GlobalVariableRelationship $c */
		foreach($withoutSelf as $c){
			$cause = addslashes($c->getCauseVariableName());
			$effect = addslashes($c->getEffectVariableName());
			$change = $c->getChangeFromBaselineString();
			$causeId = $c->getCauseVariableId();
			if($causeId === $variable->getVariableIdAttribute()){
				$effect .= " " . $change;
			} else{
				$cause .= " " . $change;
			}
			$this->addDataPoint($cause, $effect, abs($c->getChangeFromBaseline()), $c->getChartTooltip(), $c->getUrl(),
				$c->getColor());
		}
		$this->addNodes();
	}
	/**
	 * @param Collection $correlations
	 * @return Collection
	 */
	protected static function sortAndFilterCorrelations(Collection $correlations): Collection{
		$sorted = $correlations->sortByDesc(function($c, $key){
			/** @var UserVariableRelationship $c */
			return abs($c->getChangeFromBaseline());
		});
		$withoutSelf = $sorted->filter(function($c, $key){
			/** @var UserVariableRelationship $c */
			return $c->cause_variable_id !== $c->effect_variable_id;
		});
		return $withoutSelf;
	}
}
