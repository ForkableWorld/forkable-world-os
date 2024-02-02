<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseLatestNonTaggedMeasurementStartAtProperty;
class UserVariableLatestNonTaggedMeasurementStartAtProperty extends BaseLatestNonTaggedMeasurementStartAtProperty
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
            $this->throwException("latest_source_measurement_start_at is null\n\tbut ".static::NAME." is $at");
        }
        if(is_before($sourceAt,  $at)){
            $this->throwException("latest_source should not be earlier but is $sourceAt");
        }
    }
}
