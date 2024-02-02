<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMaximumRecordedValueProperty;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Variables\QMUserVariable;
class UserVariableMaximumRecordedValueProperty extends BaseMaximumRecordedValueProperty
{
    use UserVariableProperty, VariableValueTrait, UserVariableValuePropertyTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMUserVariable $uv
     * @return float
     */
    public static function calculate($uv){
        $values = $uv->getValuesWithTags();
        $maxInCommonUnit = ($values) ? max($values) : null;
        $uv->setAttribute(static::NAME, $uv->maximumRecordedValueInCommonUnit = $maxInCommonUnit);
        $uv->maximumRecordedValueInUserUnit = $uv->toUserUnit($maxInCommonUnit);
        return $maxInCommonUnit;
    }
}
