<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMinimumRecordedValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
class VariableMinimumRecordedValueProperty extends BaseMinimumRecordedValueProperty
{
    use VariableValueTrait, VariableProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param BaseModel|Variable $model
     * @return float
     */
    public static function calculate($model): ?float{
        $cv = $model->getDBModel();
        $values = $cv->pluckValidValueFromUserVariables(static::NAME);
        $val = ($values) ? min($values) : null;
        $cv->setAttribute(static::NAME, $val);
        return $val;
    }
}
