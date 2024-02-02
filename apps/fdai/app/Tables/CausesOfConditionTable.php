<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Models\CtConditionCause;
use App\Models\Variable;
class CausesOfConditionTable extends ConditionCauseTable {
	protected function getTitleAttribute(): string{
		return "Causes of " . $this->getVariable()->getTitleAttribute();
	}
	protected function getSubtitleAttribute(): string{
		return "User reported causes of " . $this->getVariable()->getTitleAttribute() . " based on their intuition. ";
	}
	protected function setDataFromVariable(){
		$variable = $this->getVariable();
		$rel = $variable->condition_causes_where_condition();
		$this->setData($rel->get());
	}
	protected function getVariableFromRow($row): Variable{
		/** @var CtConditionCause $row */
		return $row->getRelationValue('cause_variable');
	}
	protected function getNameColumnTitle(): string{
		return "Cause";
	}
	protected function getNameColumnDescription(): string{
		return $this->getSubtitleAttribute();
	}
	protected function agreeTooltip(): string{
		$v = $this->getVariable();
		return "Percent of people who think it could cause $v->name";
	}
}
