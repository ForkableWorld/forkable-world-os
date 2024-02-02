<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\VariableValueTraits;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Variable;
use App\Variables\QMVariable;
trait CauseVariableValueTrait {
	use VariableValueTrait;
	public function getVariable(): Variable{
		/** @var GlobalVariableRelationship|UserVariableRelationship $correlation */
		$correlation = $this->getParentModel();
		return $correlation->getCauseVariable();
	}
	public function getQMVariable(): QMVariable{
		return $this->getVariable()->getDBModel();
	}
}
