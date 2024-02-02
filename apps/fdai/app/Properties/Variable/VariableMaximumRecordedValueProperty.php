<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMaximumRecordedValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Variables\QMVariable;
class VariableMaximumRecordedValueProperty extends BaseMaximumRecordedValueProperty
{
    use VariableProperty, VariableValueTrait;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMVariable $model
     * @return mixed|null
     */
    public static function calculate($model){
        $values = $model->getValuesWithTags();
        if (!$values) {
            $model->logDebug("No values for maximum.  Maybe it's a tag");
            return $model->maximumRecordedValue = null;
        }
        $max = max($values);
        $model->setAttribute(static::NAME, $max);
        return $max;
    }
}
