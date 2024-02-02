<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Properties\Base\BaseThirdToLastValueProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
class UserVariableThirdToLastValueProperty extends BaseThirdToLastValueProperty
{
    use UserVariableProperty, VariableValueTrait, UserVariableValuePropertyTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    use IsCalculated;
    /**
     * @param UserVariable $model
     * @return float
     */
    public static function calculate($model): ?float{
        $values = $model->getUniqueValuesWithTagsInReverseOrder();
        $val = $values[2] ?? null;
		if($val !== null){
			$model->setAttribute(static::NAME, $val);
			$model->setLastValue($val);
		}
        return $val;
    }
}
