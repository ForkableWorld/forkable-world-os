<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\HasModel\HasUserVariable;
use App\Variables\QMCommonVariable;
trait UserVariableProperty {
	use HasUserVariable;
	public function getUserVariableId(): int{
		return $this->getUserVariable()->getId();
	}
	public function getVariableIdAttribute(): ?int{
		return $this->getUserVariable()->getVariableIdAttribute();
	}
	public function getUserVariable(): UserVariable{
		return $this->getParentModel();
	}
	public function getVariable(): Variable{
		return $this->getUserVariable()->getVariable();
	}
	public function getVariableCategoryId(): int{
		return $this->getUserVariable()->getVariableCategoryId();
	}
	public function getUserId(): ?int{
		return $this->getUserVariable()->getUserId();
	}
}
