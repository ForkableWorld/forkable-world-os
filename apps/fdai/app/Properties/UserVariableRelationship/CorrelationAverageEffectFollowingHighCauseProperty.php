<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\VariableValueTraits\EffectDailyVariableValueTrait;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseAverageEffectFollowingHighCauseProperty;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationAverageEffectFollowingHighCauseProperty extends BaseAverageEffectFollowingHighCauseProperty
{
    use CorrelationProperty, EffectDailyVariableValueTrait;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return float
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function calculate($model){
        $highPairs = $model->getHighCausePairs();
        $val = CorrelationAverageEffectProperty::calculateAverageEffectForPairSubset($highPairs);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
