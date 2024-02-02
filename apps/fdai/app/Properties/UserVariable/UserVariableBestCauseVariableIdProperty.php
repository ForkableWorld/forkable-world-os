<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\RedundantVariableParameterException;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseBestCauseVariableIdProperty;
class UserVariableBestCauseVariableIdProperty extends BaseBestCauseVariableIdProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $uv
     * @return int
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($uv): ?int{
        $best = $uv->setBestCorrelationAsEffect();
        if(!$best){return null;}
        $uv->setAttribute(static::NAME, $best->cause_variable_id);
        return $best->cause_variable_id;
    }
	/**
	 * @return void
	 * @throws InvalidAttributeException
	 * @throws RedundantVariableParameterException
	 */
	public function validate(): void {
        parent::validate();
        $new = $this->getDBValue();
        $userVariable = $this->getUserVariable();
        $current = $userVariable->variable_id;
        if($new === $current){
            $this->throwException("best cause variable must be a different variable");
        }
    }
}
