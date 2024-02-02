<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseAverageSecondsBetweenMeasurementsProperty;
use App\Utils\Stats;
use App\Variables\QMVariable;
class UserVariableAverageSecondsBetweenMeasurementsProperty extends BaseAverageSecondsBetweenMeasurementsProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param QMVariable $model
     * @return int
     */
    public static function calculate($model){
        $measurements = $model->getQMMeasurements();
        if(!$measurements || count($measurements) < 2){
            return $model->averageSecondsBetweenMeasurements = null;
        }
        $all = [];
        $byIndex = array_values($measurements);
        foreach($byIndex as $i => $iValue){
            if($i === 0){
                continue;
            }
            $previousStart = $byIndex[$i - 1]->startTime;
            $difference = $iValue->startTime - $previousStart;
            $all[] = $difference;
        }
        $average = (int)Stats::average($all);
        if($average < 1){le(static::NAME." cannot be $average");}
        $model->setAttribute(static::NAME, $average);
        return $model->averageSecondsBetweenMeasurements = $average;
    }
    public function validate(): void {
        parent::validate();
    }
}
