<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMostCommonSourceNameProperty;
class VariableMostCommonSourceNameProperty extends BaseMostCommonSourceNameProperty
{
    use VariableProperty;
    use IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param Variable $model
     * @return string
     */
    public static function calculate($model): ?string{
        $val = $model->mostCommonFromUserVariablesBasedOnNumberOfMeasurements(self::NAME);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
