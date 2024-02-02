<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseAverageReversePearsonCorrelationOverOnsetDelaysProperty;
use App\Types\QMStr;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationAverageReversePearsonCorrelationOverOnsetDelaysProperty extends BaseAverageReversePearsonCorrelationOverOnsetDelaysProperty
{
    use CorrelationProperty;
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
        if(!$correlations){le("No user_variable_relationships for CorrelationAverageReversePearsonCorrelationOverOnsetDelaysProperty!");}
        $key = QMStr::camelize(UserVariableRelationship::FIELD_FORWARD_PEARSON_CORRELATION_COEFFICIENT);
        $forward = collect($correlations)->where('onsetDelay', "<", 0);
        if(!$forward->count()){
            throw new NotEnoughDataException($model, "No reverse user_variable_relationships!");
        }
        $values = $forward->pluck($key);
        if(!$values){le("No forward correlation values!");}
        $val = $values->avg();
        if($val === null){le("No average forward correlation!");}
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
