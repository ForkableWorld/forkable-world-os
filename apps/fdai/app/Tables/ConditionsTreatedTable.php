<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Models\CtConditionTreatment;
use App\Models\Variable;
class ConditionsTreatedTable extends ConditionTreatmentsTable {
	protected function setDataFromVariable(){
		$variable = $this->getVariable();
		$rel = $variable->condition_treatments_where_treatment();
		$this->setData($rel->get());
	}
	protected function getVariableFromRow($row): Variable{
		/** @var CtConditionTreatment $row */
		return $row->getRelationValue('condition_variable');
	}
	protected function getNameColumnTitle(): string{
		return "Conditions";
	}
	protected function getNameColumnDescription(): string{
		$v = $this->getVariable();
		return "User reported treatment effectiveness of " . $v->getTitleAttribute();
	}
	protected function getTitleAttribute(): string{
		return "Treatment Effectiveness";
	}
	protected function getSubtitleAttribute(): string{
		return "User reported effectiveness ratings " . $this->getVariable()->getTitleAttribute() .
			" for various conditions.";
	}
}
