<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Astral\MeasurementBaseAstralResource;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfRawMeasurementsWithTagsJoinsChildrenProperty;
use App\Types\TimeHelper;
use App\Fields\Field;
use App\Variables\QMUserVariable;
class UserVariableNumberOfRawMeasurementsWithTagsJoinsChildrenProperty extends BaseNumberOfRawMeasurementsWithTagsJoinsChildrenProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    use IsCalculated;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        $uv = $this->getUserVariable();
        $value = $this->getDBValue();
        $withoutTags = $uv->number_of_measurements;
        if($value === null && $withoutTags){
            $this->throwException("No numberOfRawMeasurementsWithTagsJoinsChildren but number_of_raw_measurements without tags is $withoutTags");
        }
        if($value && !$uv->latest_tagged_measurement_start_at){
            $this->throwException("$value NumberOfRawMeasurementsWithTagsJoinsChildren but there is no latest_tagged_measurement_start_at");
        }
        if($uv->latest_tagged_measurement_start_at && !$value){
            $this->throwException("How can we have no numberOfRawMeasurementsWithTagsJoinsChildren but latest_tagged_measurement_start_at is ".
                TimeHelper::timeSinceHumanString($uv->latest_tagged_measurement_start_at));
        }
		if($value){
			$early = $uv->getEarliestTaggedMeasurementStartAt();
			$late = $uv->getLatestTaggedMeasurementStartAt();
			$number = $uv->getNumberOfRawMeasurementsWithTagsJoinsChildren();
			$days = (strtotime($late) - strtotime($early)) / 86400;
			$days = round($days);
			if($number > 120 && time_or_null($early) > time() - 86400){
				$this->throwException("Earliest time is today but we have more than 100 measurements!");
			}
			if($days){
				$numberPerDay = round($number/$days);
				if($number > 10 && $numberPerDay > 10){
					$uv->logError("$numberPerDay measurements per day");
				}
			}
		}
        if($uv->number_of_raw_measurements_with_tags_joins_children === null && $uv->number_of_measurements){
            $this->throwException("No number_of_raw_measurements_with_tags_joins_children but number_of_raw_measurements is $uv->number_of_measurements");
        }
    }
    public function cannotBeChangedToNull(): bool{
        $parent = $this->getUserVariable();
        if(!$parent->id){return false;}
        $uv = $this->getUserVariable();
        $latest = $uv->latest_tagged_measurement_start_at;
        return (bool)$latest;
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        //$field = parent::getField($resolveCallback, $name);
        return MeasurementBaseAstralResource::hasMany($name);
    }
}
