<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseCauseTreatmentAveragePerDayProperty;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Slim\Model\Measurement\Pair;
class CorrelationCauseTreatmentAveragePerDayProperty extends BaseCauseTreatmentAveragePerDayProperty
{
    use CorrelationProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return int
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): float {
        $pairs = $model->getFollowupPairs();
        $value = Pair::getAverageCauseValue($pairs);
        if($model->getCauseVariable()->isSum()){
            $value = $value / $model->getDurationOfActionInDays();
        }
        $model->setAttribute(static::NAME, $value);
        return $value;
    }
}
