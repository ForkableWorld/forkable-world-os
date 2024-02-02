<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\BaseModel;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseFillingValueProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
class UserVariableFillingValueProperty extends BaseFillingValueProperty
{
    use UserVariableProperty, VariableValueTrait, UserVariableValuePropertyTrait, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param int $id
     * @return BaseModel
     * @throws \App\Exceptions\AlreadyAnalyzedException
     * @throws \App\Exceptions\AlreadyAnalyzingException
     * @throws \App\Exceptions\DuplicateFailedAnalysisException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function handleTooSmall(int $id): BaseModel{
        $v = static::findParent($id);
        $v->filling_value = -1;
        $v->save();
        $v->analyzeFullyAndPostIfNecessary("Filling value was invalid");
    }
    /**
     * @param int $id
     * @return UserVariable
     */
    public static function findParent($id): ?BaseModel{
        return parent::findParent($id);
    }
    public function cannotBeChangedToNull(): bool{
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        $uv = $this->getUserVariable();
        return (bool)$uv->getFillingTypeAttribute() === BaseFillingTypeProperty::FILLING_TYPE_VALUE;
    }
	protected function validateMin(): void{
		$val = $this->getDBValue();
		if($val == -1 || $val === null){return;}
		$v = $this->getVariable();
		$min = $v->getMinimumAllowedDailyValue();
		if($val < $min){
			$this->throwInvalidVariableValueException("$val is smaller than minimum daily value $min");
		}
	}
	public function getVariable(): Variable{
		/** @var Variable|UserVariable $p */
		$p = $this->getParentModel();
		return $p->getVariable();
	}
}
