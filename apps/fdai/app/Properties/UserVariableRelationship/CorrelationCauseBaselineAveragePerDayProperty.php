<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseCauseBaselineAveragePerDayProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Utils\Stats;
use Illuminate\Support\Arr;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Slim\Model\Measurement\Pair;
class CorrelationCauseBaselineAveragePerDayProperty extends BaseCauseBaselineAveragePerDayProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param UserVariableRelationship $model
     * @return float
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): float{
        $baselinePairs = $model->getBaselinePairs();
        $value = Pair::getAverageCauseValue($baselinePairs);
        if($model->getCauseVariable()->isSum()){
            $value = $value / $model->getDurationOfActionInDays();
        }
        $model->setAttribute(static::NAME, $value);
        return $value;
    }
    public function validate(): void {
        parent::validate();
    }
}
