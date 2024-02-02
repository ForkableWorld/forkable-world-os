<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableRelationship;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Traits\PropertyTraits\CorrelationProperty;
use App\Properties\Base\BaseZScoreProperty;
use App\Traits\PropertyTraits\IsCalculated;
use App\VariableRelationships\QMUserVariableRelationship;
class CorrelationZScoreProperty extends BaseZScoreProperty
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
     */
    public static function calculate($model): ?float {
        $effectChange = CorrelationEffectFollowUpPercentChangeFromBaselineProperty::calculate($model);
        $baselineEffectRsd = CorrelationEffectBaselineRelativeStandardDeviationProperty::calculate($model);
        if($baselineEffectRsd){
            $value = abs($effectChange)/$baselineEffectRsd; // Number of standard deviations from the mean (zScore > 2 means pValue < 0.05)
            $value = round($value, 2);
            $model->setAttribute(static::NAME, $value);
            return $value;
        } else {
            $model->logError("No baselineEffectRsd to calculate zScore!");
            return null;
        }
    }
    public function showOnIndex(): bool {return true;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
