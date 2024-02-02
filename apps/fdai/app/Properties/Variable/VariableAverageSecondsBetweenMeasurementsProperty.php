<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseAverageSecondsBetweenMeasurementsProperty;
use App\Utils\Stats;
use App\Variables\QMCommonVariable;
class VariableAverageSecondsBetweenMeasurementsProperty extends BaseAverageSecondsBetweenMeasurementsProperty
{
    use VariableProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMCommonVariable $model
     * @return int
     */
    public static function calculate($model){
        $values = $model->pluckFromUserVariables(static::NAME);
        if($values){
            $average = (int)Stats::average($values);
        } else {
            $average = null;
        }
        $model->setAttribute(static::NAME, $average);
        return $average;
    }
}
