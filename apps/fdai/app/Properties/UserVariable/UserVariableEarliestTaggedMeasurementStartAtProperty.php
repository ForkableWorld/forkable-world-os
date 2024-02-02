<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseEarliestTaggedMeasurementStartAtProperty;
use App\Traits\PropertyTraits\IsTemporal;
use App\Types\TimeHelper;
use Illuminate\Http\Request;
use App\Fields\Field;
class UserVariableEarliestTaggedMeasurementStartAtProperty extends BaseEarliestTaggedMeasurementStartAtProperty
{
    use UserVariableProperty, IsTemporal;
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
    public static function fixNulls(){
        Writable::statementStatic("
            update user_variables
            set earliest_tagged_measurement_start_at = earliest_non_tagged_measurement_start_at
            where earliest_tagged_measurement_start_at is null
                and earliest_non_tagged_measurement_start_at is not null
        ");
    }
    public static function field($resolveCallback = null,
                                 string $name = null,
                                 Request $request = null): Field{
        return parent::field($resolveCallback, $name, $request);
    }
}
