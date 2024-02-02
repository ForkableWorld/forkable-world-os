<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMedianSecondsBetweenMeasurementsProperty;
use App\Utils\Stats;
use App\Variables\QMCommonVariable;
class VariableMedianSecondsBetweenMeasurementsProperty extends BaseMedianSecondsBetweenMeasurementsProperty
{
    use VariableProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param Variable|QMCommonVariable $model
     * @return mixed|void
     */
    public static function calculate($model){
        $values = $model->pluckFromUserVariables(UserVariable::FIELD_MEDIAN_SECONDS_BETWEEN_MEASUREMENTS);
        $median = ($values) ? (int)Stats::median($values) : null;
        $model->setAttribute(static::NAME, $median);
        return $median;
    }
}
