<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\Measurement;
use App\Models\UserVariable;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseEarliestNonTaggedMeasurementStartAtProperty;
use App\Types\TimeHelper;
class UserVariableEarliestNonTaggedMeasurementStartAtProperty extends BaseEarliestNonTaggedMeasurementStartAtProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function validate(): void {
        parent::validate();
        $uv = $this->getUserVariable();
        $earliestTagged = $this->getDBValue();
        if(!$earliestTagged){
            return;
        }
        $sourceAt = $uv->earliest_source_measurement_start_at;
        if(time_or_null($sourceAt) > time_or_exception($earliestTagged)){
            $this->throwException("earliest_source should not be greater but is $sourceAt");
        }
        if($uv->number_of_measurements > 100 && time_or_null($earliestTagged) > time() - 86400){
            $this->exceptionIfNotProductionAPI("earliestTaggedMeasurementTime is ".
                TimeHelper::timeSinceHumanString($earliestTagged).
                " but we have $uv->number_of_measurements raw measurements!");
        }
    }
    public static function fixInvalidRecords(){
        $qb = UserVariable::with('measurements')
            ->whereRaw('user_variables.'.static::NAME." <> min(measurements.start_at)");
        Writable::statementIfNotExists("
            create or replace view earliest_non_tagged_measurement_start_at as
            select `measurements`.`user_variable_id` AS `user_variable_id`,
                   min(`measurements`.`start_at`)    AS `earliest_non_tagged_measurement_start_at`
            from `measurements`
            group by `measurements`.`user_variable_id`;
        ");

//        $ids = Writable::selectStatic("select id
//            from `user_variables` uv join earliest_non_tagged_measurement_start_at e on uv.id = e.user_variable_id
//            where uv.earliest_non_tagged_measurement_start_at < e.earliest_non_tagged_measurement_start_at
//        ");
        $ids = [
            48268,
            54939,
            97019,
            105308,
            126066,
            200182,
        ];
        foreach($ids as $id){
            $uv = UserVariable::find($id);
            $calculated = $uv->measurements()->min(Measurement::FIELD_START_AT);
            $uv->logInfo("earliest_non_tagged_measurement_start_at is $uv->earliest_non_tagged_measurement_start_at but calculated $calculated");
            $uv->setAttribute(static::NAME, $calculated);
            $uv->save();
        }
    }
}
