<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseAverageForwardPearsonCorrelationOverOnsetDelaysProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Types\QMStr;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationAverageForwardPearsonCorrelationOverOnsetDelaysProperty extends BaseAverageForwardPearsonCorrelationOverOnsetDelaysProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return float
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): float{
        $correlations = $model->getOverDelays();
        $key = QMStr::camelize(UserVariableRelationship::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT);
        $forward = collect($correlations)->where('onsetDelay', ">", 0);
        if(!$forward->count()){
            throw new NotEnoughDataException($model, "No Forward user_variable_relationships for $model");
        }
        $values = $forward->pluck($key);
        $val = $values->avg();
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
