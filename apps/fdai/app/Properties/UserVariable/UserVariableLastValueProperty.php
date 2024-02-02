<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseLastValueProperty;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Variables\QMUserVariable;
class UserVariableLastValueProperty extends BaseLastValueProperty
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
        $values = $model->getValidUniqueValuesWithTagsInReverseOrder();
        $lastRawValueWithTags = $values[0] ?? null;
        $model->setAttribute(static::NAME, $lastRawValueWithTags);
        // DON'T VALIDATE HERE BECAUSE WE HAVE TO WAIT UNTIL THE OTHER LastValues ARE SET
        return $lastRawValueWithTags;
    }
}
