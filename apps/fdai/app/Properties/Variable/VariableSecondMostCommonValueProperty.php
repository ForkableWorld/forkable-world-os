<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseSecondMostCommonValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Utils\Stats;
use App\Types\QMStr;
use App\Variables\QMCommonVariable;
class VariableSecondMostCommonValueProperty extends BaseSecondMostCommonValueProperty
{
    use VariableProperty, VariableValueTrait;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    use IsCalculated;
    /**
     * @param QMCommonVariable $model
     * @return float
     */
    public static function calculate($model): ?float{
        $values = $model->getValidValues();
        $val = ($values) ? Stats::secondMostCommonValue($values): null;
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
