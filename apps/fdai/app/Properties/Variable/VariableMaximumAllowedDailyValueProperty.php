<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Logging\QMLog;
use App\Models\Measurement;
use App\Models\Variable;
use App\Traits\VariableValueTraits\DailyVariableValueTrait;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMaximumAllowedDailyValueProperty;
class VariableMaximumAllowedDailyValueProperty extends BaseMaximumAllowedDailyValueProperty
{
    use VariableProperty, DailyVariableValueTrait;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public static function fixInvalidRecords(){
        $ids = Variable::whereNotNull(Variable::FIELD_MAXIMUM_ALLOWED_VALUE)
            ->where(Variable::FIELD_NUMBER_OF_USER_VARIABLES, ">", 10)
            ->pluck('id');
        $byVariable = [];
        foreach($ids as $id){
            $v = Variable::findInMemoryOrDB($id);
            $max = $v->maximum_allowed_value;
            try {
                $measurements = $v->measurements()
                    ->where(Measurement::FIELD_VALUE,  ">", $max)
                    ->get();
            } catch (\Throwable $e){
                $v->maximum_allowed_value;
                QMLog::info(__METHOD__.": ".$e->getMessage());
                continue;
            }
            /** @var Measurement $measurement */
            foreach($measurements as $measurement){
                $byVariable[$v->name][$measurement->getUnitAbbreviatedName()][] = $measurement->value;
                //$measurement->logValue();
                //$measurement->value = $measurement->value/1000;
                //$measurement->save();
            }
        }
        QMLog::print($byVariable, "Too Big");
    }
}
