<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Tables;
use App\Models\CtTreatmentSideEffect;
use App\Models\Variable;
use App\UI\FontAwesome;
class TreatmentsWithSideEffectTable extends TreatmentSideEffectTable {
	/**
	 * @param $row
	 * @return Variable
	 */
	protected function getVariableFromRow($row): Variable{
		/** @var CtTreatmentSideEffect $row */
		return $row->getRelationValue('treatment_variable');
	}
	public function addValueColumn(){
		$this->column()->title('Percent of Reports ' . FontAwesome::html(FontAwesome::QUESTION_CIRCLE) . " ")
			->attr('th', 'title', "Percent of total side effect reports for this treatment ")->value(function($tse){
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
		$treatment_side_effects = $variable->ct_treatment_side_effects_where_side_effect_variable()->get();
		$this->setData($treatment_side_effects);
	}
	protected function getNameColumnTitle(): string{
		return 'Treatment';
	}
	protected function getNameColumnDescription(): string{
		$v = $this->getVariable();
		return "Treatments reported to cause " . $v->getTitleAttribute();
	}
	protected function getTitleAttribute(): string{
		return "Treatments Causing " . $this->getVariable()->getTitleAttribute();
	}
	protected function getSubtitleAttribute(): string{
		return "User-reported treatments that exhibited the side effect of " . $this->getVariable()->getTitleAttribute();
	}
}
