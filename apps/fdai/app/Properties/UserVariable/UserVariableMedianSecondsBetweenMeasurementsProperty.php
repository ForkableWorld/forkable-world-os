<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseMedianSecondsBetweenMeasurementsProperty;
use App\Utils\Stats;
use App\Variables\QMUserVariable;
class UserVariableMedianSecondsBetweenMeasurementsProperty extends BaseMedianSecondsBetweenMeasurementsProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $model
     * @return mixed
     */
    public static function calculate($model){
        $measurements = $model->getQMMeasurements();
        if(!$measurements || count($measurements) < 2){
            return $model->medianSecondsBetweenMeasurements = null;
        }
        $measurements = array_values($measurements);
        $all = [];
        foreach($measurements as $i => $iValue){
            if($i === 0){
                continue;
            }
            try {
                $difference = $iValue->startTime - $measurements[$i - 1]->startTime;
                $all[] = $difference;
            } catch (\Throwable $e){
                le($e);
            }
        }
        $median = (int)Stats::median($all);
        $model->setAttribute(static::NAME, $median);
        return $median;
    }
}
