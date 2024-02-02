<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NotEnoughOverlappingDataException;
use App\Models\UserVariableRelationship;
use App\Properties\Measurement\MeasurementStartTimeProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseEffectNumberOfProcessedDailyMeasurementsProperty;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationEffectNumberOfProcessedDailyMeasurementsProperty extends BaseEffectNumberOfProcessedDailyMeasurementsProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $uc
     * @return mixed|void
     * @throws \App\Exceptions\NotEnoughMeasurementsForCorrelationException
     * @throws \App\Exceptions\NotEnoughOverlappingDataException
     */
    public static function calculate($uc){
        $eMeasurements = $uc->getMeasurementsFromEffectVariable();
        $val = count($eMeasurements);
        $prop = new static();
        if($val < $prop->minimum){
            $message = "We only have $val effect measurements but need at least $prop->minimum. ";
            if($uc->hasId()){
                $original  = $uc->effectNumberOfProcessedDailyMeasurements;
                $message .= "\nThis correlation was last analyzed $uc->analysisEndedAt and the original value was $original. ".
                "\nYou can delete this at ".$uc->getDataLabShowUrl();
            }
            throw new NotEnoughMeasurementsForCorrelationException($message, $uc);
        }
        $uc->setAttribute(static::NAME, $val);
        return $val;
    }
}
