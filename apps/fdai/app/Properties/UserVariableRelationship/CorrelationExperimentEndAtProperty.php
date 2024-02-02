<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\NotEnoughOverlappingDataException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseExperimentEndAtProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Utils\AppMode;
class CorrelationExperimentEndAtProperty extends BaseExperimentEndAtProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param UserVariableRelationship $model
     * @return string
     * @throws NotEnoughOverlappingDataException
     */
    public static function calculate($model): string{
        $cause = $model->getCauseUserVariable();
        $effect = $model->getEffectUserVariable();
        $cLatestFilling = $cause->getLatestFillingAt();
        if($cause->measurementsAreSet() || !AppMode::isApiRequest()){
            $cMeasurements = $cause->getValidDailyMeasurementsWithTagsAndFilling();
            if(!$cMeasurements){throw new NotEnoughOverlappingDataException($model);}
        }
        $effectLatestFilling = $effect->getLatestFillingAt();
        $latestFilling = min($cLatestFilling, $effectLatestFilling);
        $duration = $cause->getDurationOfAction();
        $onset = $effect->getOnsetDelay();
        $experimentEnd = db_date(strtotime($latestFilling) + $duration + $onset);
        if(strtotime($experimentEnd) > time()){$experimentEnd = now_at();}
        $model->setAttribute(static::NAME, $experimentEnd);
        try {
            $model->validateAttribute(static::NAME);
        } catch (InvalidAttributeException $e) {le($e);}
		if(strtotime($experimentEnd) > time()){le('strtotime($experimentEnd) > time()');}
        return $experimentEnd;
    }
}
