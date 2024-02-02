<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Exceptions\InsufficientVarianceException;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseEffectChangesProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\Utils\Stats;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationEffectChangesProperty extends BaseEffectChangesProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return int
     * @throws InsufficientVarianceException
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     */
    public static function calculate($model): int{
        $arr = $model->getEffectValues();
        $val = Stats::countChanges($arr);
        self::validateByValue($val, $model);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    /**
     * @param int $value
     * @param QMUserVariableRelationship $model
     * @throws InsufficientVarianceException
     */
    public static function validateByValue($value, $model = null){
        if($value < CorrelationCauseChangesProperty::MINIMUM_CHANGES){
            $params = $model->getOnsetDelayDurationHumanString();
            throw new InsufficientVarianceException($model,
                "There are only $value changes in the effect values from $params paired data. ".
                CorrelationCauseChangesProperty::MINIMUM_CHANGES." are required. ");
        }
    }
}
