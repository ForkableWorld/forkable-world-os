<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseCauseTreatmentAveragePerDurationOfActionProperty;
use App\Utils\Stats;
use Illuminate\Support\Arr;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationCauseTreatmentAveragePerDurationOfActionProperty extends BaseCauseTreatmentAveragePerDurationOfActionProperty
{
    use CorrelationProperty;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    use \App\Traits\PropertyTraits\IsCalculated;
    /**
     * @param QMUserVariableRelationship $model
     * @return int
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): float {
        $pairs = $model->getFollowupPairs();
        $causeTreatmentValues = Arr::pluck($pairs, 'causeMeasurementValue');
        $value = Stats::average($causeTreatmentValues, 3);
        $model->setAttribute(static::NAME, $value);
        return $value;
    }
}
