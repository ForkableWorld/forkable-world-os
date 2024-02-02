<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
trait HasGlobalVariableRelationship {
	/**
	 * @return GlobalVariableRelationship
	 */
	public function findGlobalVariableRelationship(): ?GlobalVariableRelationship{
		return GlobalVariableRelationship::findByData([
			GlobalVariableRelationship::FIELD_CAUSE_VARIABLE_ID => $this->getCauseVariableId(),
			GlobalVariableRelationship::FIELD_EFFECT_VARIABLE_ID => $this->getEffectVariableId(),
		]);
	}
	public function getGlobalVariableRelationshipId(): ?int{
		if(property_exists($this, 'aggregateCorrelationId')){
			return $this->aggregateCorrelationId;
		}
		return $this->getAttribute('global_variable_relationship_id');
	}
	public function getGlobalVariableRelationshipButton(): QMButton{
		/** @var GlobalVariableRelationship $aggregateCorrelation */
		if($this instanceof BaseModel){
			$aggregateCorrelation = $this->relations['global_variable_relationship'] ?? null;
		} else{
			$aggregateCorrelation = $this->aggregateCorrelation ?? null;
		}
		if($aggregateCorrelation){
			return $aggregateCorrelation->getButton();
		}
		return GlobalVariableRelationship::generateDataLabShowButton($this->getGlobalVariableRelationshipId());
	}
	/**
	 * @throws \App\Exceptions\NotEnoughDataException
	 */
	public function getGlobalVariableRelationshipNameLink(): string{
		return $this->findGlobalVariableRelationship()->getDataLabDisplayNameLink();
	}
}
