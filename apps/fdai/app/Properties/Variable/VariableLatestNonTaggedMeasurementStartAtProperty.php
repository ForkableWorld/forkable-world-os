<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseLatestNonTaggedMeasurementStartAtProperty;
class VariableLatestNonTaggedMeasurementStartAtProperty extends BaseLatestNonTaggedMeasurementStartAtProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public function cannotBeChangedToNull(): bool{
        $uv = $this->getVariable();
        $num = $uv->number_of_measurements;
        return (bool)$num;
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        $this->validateTime();
        $v  = $this->getVariable();
        $earliest = $v->earliest_non_tagged_measurement_start_at;
        $latest = $this->getDBValue();
        $this->assertEarliestBeforeLatest($earliest, $latest);
    }
    public function getLatestUnixTime(): int {
        return MeasurementStartTimeProperty::generateLatestUnixTime();
    }
    public function getEarliestUnixTime(): int{
        return MeasurementStartTimeProperty::generateEarliestUnixTime();
    }
}
