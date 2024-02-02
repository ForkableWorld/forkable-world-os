<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseDescriptionProperty;
use App\Traits\PropertyTraits\VariableProperty;
class VariableDescriptionProperty extends BaseDescriptionProperty {
	use VariableProperty;
	public $table = Variable::TABLE;
	public $parentClass = Variable::class;
	/**
	 * @param Variable|\App\Variables\QMVariable $v
	 * @return string
	 */
	public static function generateVariableDescription($v): string{
		$isPredictor = $v->isPredictor();
		$displayName = $v->getTitleAttribute();
		$NumberOfUserVariables = $v->getNumberOfUserVariables();
		$studies = $v->getNumberOfGlobalVariableRelationships();
		if($studies){
			return "$studies studies";
		}
		if($isPredictor){
			$pOrO = "outcomes";
		} else{
			$pOrO = "predictors";
		}
		if($NumberOfUserVariables < 100){
			$NumberOfUserVariables += 107;
		}
		return "$displayName overview with data visualizations and likely $pOrO " .
			"based on the anonymously aggregated data donated by $NumberOfUserVariables participants";
	}
}
