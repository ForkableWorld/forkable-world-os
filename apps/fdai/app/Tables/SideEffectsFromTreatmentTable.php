<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Models\CtTreatmentSideEffect;
use App\Models\Variable;
use App\UI\FontAwesome;
class SideEffectsFromTreatmentTable extends TreatmentSideEffectTable {
	public function addValueColumn(){
		$v = $this->getVariable();
		$this->column()->title('Percent of Reports ' . $this->getVariable()->getTitleAttribute() .
				FontAwesome::html(FontAwesome::QUESTION_CIRCLE) . " ")
			->attr('th', 'title', "Percent of total side effect reports from " . $v->getTitleAttribute())
			->value(function($tse){
				/** @var CtTreatmentSideEffect $tse */
				$change = $tse->votes_percent;
				return $change . "%";
			})->attr('td', 'data-order', function($tse){
				/** @var CtTreatmentSideEffect $tse */
				return $tse->votes_percent;
			})->add();
	}
	protected function setDataFromVariable(){
		$variable = $this->getVariable();
		$treatment_side_effects = $variable->treatment_side_effects_where_treatment()->get();
		$this->setData($treatment_side_effects);
	}
	protected function getVariableFromRow($row): Variable{
		/** @var CtTreatmentSideEffect $row */
		$link = $row->getRelationValue('side_effect_variable');
		return $link;
	}
	protected function getNameColumnTitle(): string{
		return 'Side Effect';
	}
	protected function getNameColumnDescription(): string{
		$v = $this->getVariable();
		return "User reported side effects from " . $v->getTitleAttribute();
	}
	protected function getTitleAttribute(): string{
		return "Side Effects";
	}
	protected function getSubtitleAttribute(): string{
		return "User-reported side effects of " . $this->getVariable()->getTitleAttribute();
	}
}
