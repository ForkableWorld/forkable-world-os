<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseLatestTaggedMeasurementStartAtProperty;
class UserVariableLatestTaggedMeasurementStartAtProperty extends BaseLatestTaggedMeasurementStartAtProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function validate(): void {
        parent::validate();
        $uv = $this->getUserVariable();
        $at = $this->getDBValue();
        if(!$at){return;}
        $sourceAt = $uv->latest_source_measurement_start_at;
        if(!$sourceAt){
            // This can happen when measurements are generated like Daily Grades $this->throwException("latest_source_measurement_start_at is null\n\tbut ".static::NAME." is $at");
        }
        if($sourceAt && is_before($sourceAt,  $at)){
            $this->throwException("latest_source should not be earlier but is $sourceAt");
        }
    }
    public static function fixNulls(){
        Writable::statementStatic("
            update user_variables
            set latest_tagged_measurement_start_at = latest_non_tagged_measurement_start_at
            where latest_tagged_measurement_start_at is null
                and latest_non_tagged_measurement_start_at is not null
        ");
    }
}
