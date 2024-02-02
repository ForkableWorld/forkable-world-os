<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Measurement;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseNumberOfMeasurementsProperty;
use App\Variables\QMCommonVariable;
class VariableNumberOfMeasurementsProperty extends BaseNumberOfMeasurementsProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param QMCommonVariable $model
     * @return int
     */
    public static function calculate($model): int {
        if($model->allTaggedMeasurementsAreSet()){
            $val = count($model->getQMMeasurements());
        } else {
            $val = Measurement::whereVariableId($model->getVariableIdAttribute())->count();
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
