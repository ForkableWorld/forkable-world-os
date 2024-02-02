<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
class UserVariableCombinationOperationProperty extends BaseCombinationOperationProperty
{
    use UserVariableProperty, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function pluckAndSetDBValue($data, bool $fallback = false){
        $val = $this->pluck($data);
        if($val !== null){
            $variable = $this->getVariable();
            if($variable->combination_operation !== $val){
                return $this->processAndSetDBValue($val);
            }
        }
        return null;
    }
    /**
     * @return bool|void
     * @throws \App\Exceptions\InvalidAttributeException
     * @throws \App\Exceptions\ModelValidationException
     */
    public function validate(): void {
        parent::validate();
        if($co = $this->getDBValue()){
            $variable = $this->getVariable();
            $commonCO = $variable->combination_operation;
            if($commonCO === $co){
                $this->throwException("user-defined value should not be set because it's the same as that of the common variable");
            }
            $co = strtoupper($co);
            $u = $this->getCommonUnit();
            if($co === self::COMBINATION_SUM && !$u->canBeSummed()){
                $this->logError("Invalid SUM combination operation for unit $u->name!  Changing to MEAN in DB... ");
                $uv = $this->getUserVariable();
                $uv->setRawAttribute(static::NAME, null);
                if($commonCO === self::COMBINATION_SUM){
                    $variable->combination_operation = self::COMBINATION_MEAN;
                    $variable->save();
                }
            }
        }
    }
	/**
	 * @param $value
	 * @return string|null
	 */
	public function toDBValue($value): ?string{
        $variable = $this->getVariable();
        if($value === $variable->combination_operation){
            return null;
        }
        return $value;
    }
}
