<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BasePredictsHighEffectChangeProperty;
use App\Utils\Stats;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationPredictsHighEffectChangeProperty extends BasePredictsHighEffectChangeProperty
{
    use CorrelationProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return float
     */
    public static function calculate($model){
        $averageEffect = $model->getOrCalculateAverageEffect();
        $v = $model->getEffectQMVariable();
        $spread = $v->getSpread(); // Need to use spread as denominator in case $averageEffect is 0
        $effectValuesExpectedToBeHigherThanAverage = $model->getEffectValuesExpectedToBeHigherThanAverage();
        $averageOfEffectValuesExpectedToBeHigherThanAverage = Stats::average($effectValuesExpectedToBeHigherThanAverage);
        $predictsHighEffectChange = round(100 * ($averageOfEffectValuesExpectedToBeHigherThanAverage - $averageEffect) / $spread, 2);
        if($averageOfEffectValuesExpectedToBeHigherThanAverage < $averageEffect){
            $model->addWarning("The average of effect values expected to be higher than average ($averageOfEffectValuesExpectedToBeHigherThanAverage)".
                " are actually lower than the average ($averageEffect). This suggests a weak relationship or insufficient data.");
        }
        if($predictsHighEffectChange === null){
            $model->logAndSetCorrelationError('The change in effect from average following days when the effect should be higher than average is null.  ');
        } else {
            $predictsHighEffectChange = (int)$predictsHighEffectChange;
        }
        if($predictsHighEffectChange === 0){
            $model->addWarning('The change in effect from average following days when the effect should be higher than average is 0%.  ');
        }
        $model->setAttribute(static::NAME, $predictsHighEffectChange);
        return $predictsHighEffectChange;
    }
}
