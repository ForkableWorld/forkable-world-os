<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseOptimalPearsonProductProperty;
use App\Utils\Stats;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationOptimalPearsonProductProperty extends BaseOptimalPearsonProductProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMUserVariableRelationship $model
     * @return float|null
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function calculate($model) {
        QMLog::info("TODO: Implement calculate in ".static::class);
        return null;
        // TODO: Implement calculate() method.
        //$model->setAttribute(static::NAME, $value);
        //return $value;
    }
    /**
     * @param array $causeMeasurementValueArrayFromPairs
     * @param QMUserVariableRelationship $c
     * @return float
     */
    public static function calculateOptimalPearsonProduct(array $causeMeasurementValueArrayFromPairs, $c){
        if (!isset($c->avgDailyValuePredictingHighOutcome)) {
            $c->setInternalErrorMessage('The valuePredicting High Outcome is not defined');
            return null;
        }
        if (!isset($c->avgDailyValuePredictingLowOutcome)) {
            $c->setInternalErrorMessage('Value Predicting Low Outcome is not defined');
            return null;
        }
        $stdDeviationOfCauseValues = Stats::standardDeviation($causeMeasurementValueArrayFromPairs);
        if ($stdDeviationOfCauseValues && $c->avgDailyValuePredictingHighOutcome !== null
            && $c->avgDailyValuePredictingLowOutcome !== null) {
            $effectDiff = $c->avgDailyValuePredictingHighOutcome -
                $c->avgDailyValuePredictingLowOutcome;
            return ($c->correlationCoefficient - $c->reversePearsonCorrelationCoefficient) *
                $effectDiff / $stdDeviationOfCauseValues;
        }
        return null;
    }
}
