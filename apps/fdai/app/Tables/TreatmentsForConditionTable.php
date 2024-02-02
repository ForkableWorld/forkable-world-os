<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Models\CtConditionTreatment;
use App\Models\CtTreatmentSideEffect;
use App\Models\Variable;
class TreatmentsForConditionTable extends ConditionTreatmentsTable {
	protected $orderColumnIndex = 6;
	protected function setDataFromVariable(){
		$variable = $this->getVariable();
		$rel = $variable->condition_treatments_where_condition()->where(CtConditionTreatment::FIELD_POPULARITY, ">", 0);
		$this->setData($rel->get());
	}
	protected function getVariableFromRow($row): Variable{
		/** @var CtTreatmentSideEffect $row */
		return $row->getRelationValue('treatment_variable');
	}
	protected function getNameColumnTitle(): string{
		return 'Treatment';
	}
	protected function getNameColumnDescription(): string{
		$v = $this->getVariable();
		return "User reported treatment effectiveness for " . $v->getTitleAttribute();
	}
	protected function getTitleAttribute(): string{
		return "Treatments";
	}
	protected function getSubtitleAttribute(): string{
		return "User-reported effectiveness in treating " . $this->getVariable()->getTitleAttribute();
	}
}
