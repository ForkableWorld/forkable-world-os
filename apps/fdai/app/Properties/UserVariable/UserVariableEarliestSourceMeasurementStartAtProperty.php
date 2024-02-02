<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseEarliestSourceMeasurementStartAtProperty;
class UserVariableEarliestSourceMeasurementStartAtProperty extends BaseEarliestSourceMeasurementStartAtProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function cannotBeChangedToNull(): bool{
        $parent = $this->getParentModel();
        if(!$parent->id){return false;}
        $uv = $this->getUserVariable();
        $num = $uv->number_of_measurements;
        return (bool)$num;
    }
    /**
     * @param UserVariable $uv
     * @return string
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public static function calculate($uv): ?string{
        $measurements = $uv->getQMMeasurements();
        $at = null;
        if($measurements){
            if($clientIds = $uv->getClientIds()){
                $at = UserVariableClient::whereUserId($uv->getUserId())
                    ->whereIn(UserVariableClient::FIELD_CLIENT_ID, $clientIds)
                    ->min(UserVariableClient::FIELD_EARLIEST_MEASUREMENT_AT);
            } else {
                $at = $uv->earliest_tagged_measurement_start_at;
                // It's probably a tag variable with no user variable clients
            }
            $earliestMeasurementAt = UserVariableEarliestNonTaggedMeasurementStartAtProperty::calculate($uv);
			if(!$at){
				$at = $uv->earliest_tagged_measurement_start_at = $earliestMeasurementAt;
			}
            if($earliestMeasurementAt < $at){
                UserVariableClient::updateByUserVariable($uv);
                $at = $earliestMeasurementAt;
            }
            $at = db_date($at);
        }
        $uv->setAttribute(static::NAME, $at);
        $l = $uv->l();
        $fromL = $l->getAttribute(static::NAME);
        if($fromL !== $at){
            $uv->setAttribute(static::NAME, $at);
            $l = $uv->l();
            $fromL = $l->getAttribute(static::NAME);
        }
        return $at;
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        /** @var UserVariable $variable */
        $variable = $this->getParentModel();
        $earliestSource = $this->getDBValue();
        $this->assertEarliestBeforeLatest($earliestSource, $variable->latest_source_measurement_start_at);
        $earliestMeasurement = $variable->earliest_non_tagged_measurement_start_at;
        if($earliestMeasurement && $earliestMeasurement < $earliestSource){
            $this->throwException("Earliest measurement $earliestMeasurement should not be less than earliest source");
        }
    }
}
