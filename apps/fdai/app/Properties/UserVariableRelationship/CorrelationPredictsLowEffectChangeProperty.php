<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BasePredictsLowEffectChangeProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Utils\Stats;
class CorrelationPredictsLowEffectChangeProperty extends BasePredictsLowEffectChangeProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return float
     * @throws InsufficientVarianceException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws TooSlowToAnalyzeException
     */
    public static function calculate($model){
        $averageEffect = $model->getOrCalculateAverageEffect();
        $v = $model->getEffectQMVariable();
        $spread = $v->getSpread(); // Need to use spread as denominator in case $averageEffect is 0
        $effectValuesExpectedToBeLowerThanAverage = $model->getEffectValuesExpectedToBeLowerThanAverage();
        $averageOfEffectValuesExpectedToBeLowerThanAverage = Stats::average($effectValuesExpectedToBeLowerThanAverage);
        if($averageOfEffectValuesExpectedToBeLowerThanAverage > $averageEffect){
            $model->addWarning("The average of effect values expected to be lower than average ($averageOfEffectValuesExpectedToBeLowerThanAverage)".
                " are actually higher than the average ($averageEffect). This suggests a weak relationship or insufficient data.");
        }
        if(!$spread){le('!$spread');}
        $value = round(100 * ($averageOfEffectValuesExpectedToBeLowerThanAverage - $averageEffect) / $spread, 2);
        if(!$value === null){
            $model->logAndSetCorrelationError('The change in outcome following values in which the predictor suggests low outcomes is null.');
        } else {
            $value = (int)$value;
        }
        $model->setAttribute(static::NAME, $value);
        return $value;
    }
    /**
     * @throws InsufficientVarianceException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws TooSlowToAnalyzeException
     */
    public static function fixNullPredictsLowEffectChange()
    {
        $rows = QMUserVariableRelationship::readonly()->whereNull(UserVariableRelationship::FIELD_PREDICTS_LOW_EFFECT_CHANGE)->getArray();
        foreach ($rows as $row) {
            $c =
                QMUserVariableRelationship::findByNamesOrIds($row->user_id, $row->cause_variable_id,
                    $row->effect_variable_id);
            $c->analyzeFully(__FUNCTION__);
		if($c->predictsLowEffectChange === null){le('$c->predictsLowEffectChange === null');}
            $c =
                QMUserVariableRelationship::findByNamesOrIds($row->user_id, $row->cause_variable_id,
                    $row->effect_variable_id);
		if($c->predictsLowEffectChange === null){le('$c->predictsLowEffectChange === null');}
        }
    }
}
