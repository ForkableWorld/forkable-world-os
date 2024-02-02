<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
use App\VariableRelationships\QMUserVariableRelationship;
class PairsOverTimeHighstock extends MultivariateHighstock {
	protected $correlationId;
	protected $correlation;
	/**
	 * @param QMUserVariableRelationship|int $correlationOrId
	 * @param QMChart|null $QMChart
	 */
	public function __construct($correlationOrId = null, QMChart $QMChart = null){
		if(!$this->qmChart){$this->qmChart = $QMChart;}
		if(is_int($correlationOrId)){
			$this->correlationId = $correlationOrId;
		} else{
			/** @var QMUserVariableRelationship $correlationOrId */
			$this->correlationId = $correlationOrId->id;
			$this->correlation = $correlationOrId;
		}
		parent::__construct([], $QMChart);
	}
	public function getVariables(): array{
		$c = $this->getCorrelation();
		return [$c->getCauseQMVariable(), $c->getEffectQMVariable()];
	}
	/**
	 * @return QMUserVariableRelationship
	 */
	public function getCorrelation(): QMUserVariableRelationship{
		if($c = $this->correlation){
			return $c;
		}
		if($this->correlationId){
			return $this->correlation = QMUserVariableRelationship::find($this->correlationId);
		}
		throw new \LogicException("No correlation");
	}
}
