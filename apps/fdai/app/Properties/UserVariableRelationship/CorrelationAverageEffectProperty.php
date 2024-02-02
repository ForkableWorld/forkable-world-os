<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseAverageEffectProperty;
use App\Utils\Stats;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Slim\Model\Measurement\Pair;
class CorrelationAverageEffectProperty extends BaseAverageEffectProperty
{
    use CorrelationProperty;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param Pair[] $pairs
     * @return float|null
     */
    public static function calculateAverageEffectForPairSubset($pairs): ?float{
        $effectValues = array_map(static function ($o) {
            return $o->effectMeasurementValue;
        }, $pairs);
        $numberOfEffectValues = count($effectValues);
        if ($numberOfEffectValues < 1) {
            QMLog::debug('there are not enough measurements for to get average effect.');
            return null;
        }
        return Stats::average($effectValues);
    }
    /**
     * @param QMVariableRelationship $model
     * @return float
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): float {
        $values = $model->getEffectValues();
        $avg = Stats::average($values);
        $model->setAttribute(static::NAME, $avg);
        return $avg;
    }
}
