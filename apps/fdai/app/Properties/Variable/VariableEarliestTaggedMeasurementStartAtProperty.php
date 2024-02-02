<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseEarliestTaggedMeasurementStartAtProperty;
class VariableEarliestTaggedMeasurementStartAtProperty extends BaseEarliestTaggedMeasurementStartAtProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        $this->validateTime();
        $this->assertEarliestBeforeLatest($this->getDBValue(), $this->getVariable()->latest_tagged_measurement_start_at);
    }
    public function getLatestUnixTime(): int {
        return MeasurementStartTimeProperty::generateLatestUnixTime();
    }
    public function getEarliestUnixTime(): int{
        return MeasurementStartTimeProperty::generateEarliestUnixTime();
    }
    public function cannotBeChangedToNull(): bool{
        $uv = $this->getVariable();
        $num = $uv->number_of_measurements;
        return (bool)$num;
    }
}
