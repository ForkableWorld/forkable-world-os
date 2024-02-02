<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNewestDataAtProperty;
use App\Types\QMArr;
use App\Variables\QMCommonVariable;
class VariableNewestDataAtProperty extends BaseNewestDataAtProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use IsCalculated;
    /**
     * @param QMCommonVariable $model
     * @return string
     */
    public static function calculate($model): ?string{
        $val = $model->getUserVariables();
        if(!$val){
            $model->logError("No user variables to calculated ".static::NAME);
            return null;
        }
        $val = QMArr::max($val, UserVariable::FIELD_UPDATED_AT);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
