<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\IsCalculated;
use App\Traits\VariableValueTraits\CauseDailyVariableValueTrait;
use App\Properties\Base\BaseAverageDailyHighCauseProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Types\QMArr;
use App\Utils\Stats;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Slim\Model\Measurement\Pair;
class CorrelationAverageDailyHighCauseProperty extends BaseAverageDailyHighCauseProperty
{
    use CauseDailyVariableValueTrait, CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param float $highCauseMinimum
     * @param Pair[] $allPairs
     * @return Pair[] array
     */
    public static function getHighCausePairs($highCauseMinimum, $allPairs): array{
        $highCausePairs = $allPairs;
        foreach ($highCausePairs as $i => $iValue) {
            // If we use <=, it's very limiting in the number of user_variable_relationships that get calculated
            if ($iValue->causeMeasurementValue < $highCauseMinimum) {
                unset($highCausePairs[$i]);
            }
        }
        $highCausePairs = array_values($highCausePairs);
        return $highCausePairs;
    }
    /**
     * @param QMUserVariableRelationship $model
     * @return mixed|void
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model){
        $pairs = $model->getHighCausePairs();
        $val = QMUserVariableRelationship::calculateAverageCauseForPairSubset($pairs);
        $cause = $model->getCauseQMVariable();
        $dailyMeasurements = $cause->getValidDailyMeasurementsWithTags();
        $max = QMArr::max($dailyMeasurements, 'value');
        if(Stats::greaterThan($val, $max)){ // For some reason greater than doesn't work right for some floats
            le(static::NAME." ($val)\ncannot be greater than max daily value $max\nfor $cause");
        }
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
