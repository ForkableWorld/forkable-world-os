<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseEffectFollowUpPercentChangeFromBaselineProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationEffectFollowUpPercentChangeFromBaselineProperty extends BaseEffectFollowUpPercentChangeFromBaselineProperty
{
    use CorrelationProperty;
    use IsCalculated;
    public $table = UserVariableRelationship::TABLE;
    public $parentClass = UserVariableRelationship::class;
    /**
     * @param QMUserVariableRelationship $model
     * @return float|null
     * @throws \App\Exceptions\NotEnoughDataException
     * @throws \App\Exceptions\TooSlowToAnalyzeException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function calculate($model) {
        $baselineEffectMean = CorrelationEffectBaselineAverageProperty::calculate($model);
        $followupEffectMean = CorrelationEffectFollowUpAverageProperty::calculate($model);
        if((float)$baselineEffectMean === (float)0 || $model->getEffectVariableCommonUnit()->isPercent()){
            $val = $followupEffectMean - $baselineEffectMean;
        } else {
            $val = ($followupEffectMean - $baselineEffectMean)/$baselineEffectMean * 100;
        }
        $val = round($val, 1);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
