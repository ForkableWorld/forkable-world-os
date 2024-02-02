<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\RedundantVariableParameterException;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Traits\HardCodableProperty;
use App\Traits\HasModel\HasVariable;
trait VariableProperty {
	use HasVariable, HardCodableProperty;
	public function getVariableIdAttribute(): ?int{
		return $this->getParentModel()->getId();
	}
	public function getVariable(): Variable{
		return $this->getParentModel();
	}
	public function getVariableCategoryId(): int{
		return $this->getVariable()->getVariableCategoryId();
	}

    /**
     * @return void
     * @throws RedundantVariableParameterException
     */
    protected function ensureValueDiffersFromVariableCategory(): void
    {
        $val = $this->getDBValue();
        $v = $this->getVariable();
        $cat = $v->getVariableCategory();
        $catVal = $cat->getRawAttribute($this->name);
        if ($val === $catVal) {
            $ruleDescription = "should not be saved to DB if the same as category value";
            throw new RedundantVariableParameterException($v, $this, $ruleDescription);
        }
    }
}
