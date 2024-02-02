<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfMeasurementsProperty;
use App\Variables\QMUserVariable;
class UserVariableNumberOfMeasurementsProperty extends BaseNumberOfMeasurementsProperty
{
    use UserVariableProperty;
    use IsCalculated;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param UserVariable $model
     * @return int
     */
    public static function calculate($model): int{
        $measurements = $model->getQMMeasurements();
        $number = count($measurements);
        $model->setAttribute(static::NAME, $number);
        return $number;
    }
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        if($withoutTags = $this->getDBValue()){
            $uv = $this->getUserVariable();
            $this->checkNumberTagged($uv, $withoutTags);
            $dbm = QMUserVariable::findInMemory($uv->id);
            if($dbm && $dbm->measurementsAreSet()){
                $this->validateAgainstSetMeasurements($dbm, $withoutTags);
            }
        }
    }
    /**
     * @param UserVariable $uv
     * @param $withoutTags
     * @throws \App\Exceptions\InvalidAttributeException
     */
    private function checkNumberTagged(UserVariable $uv, $withoutTags): void{
        $withTags = $uv->number_of_raw_measurements_with_tags_joins_children;
        if(!$withTags){
            $this->throwException("No number_of_raw_measurements_with_tags_joins_children but number_of_raw_measurements without tags is $withoutTags");
        }
    }
    /**
     * @param QMUserVariable $dbm
     * @param $withoutTags
     * @throws \App\Exceptions\InvalidAttributeException
     */
    private function validateAgainstSetMeasurements(QMUserVariable $dbm, $withoutTags): void{
        $byStartTime = $dbm->getNewAndExistingMeasurementsIndexedByStartAt();
        $actual = count($byStartTime);
        if($actual > $withoutTags){
            $this->logInfo("Setting ".
                static::NAME.
                " to $actual because we're probably importing and upserting before calculating...");
            $this->setRawAttribute($actual);
            return;
        }
        if($withoutTags !== $actual){
            $message = "we have $actual Raw Measurements for real";
            // TODO: throw exception
            if($exception = false){
                $this->throwException($message);
            }else{
                $this->logError($message);
            }
        }
    }

}
