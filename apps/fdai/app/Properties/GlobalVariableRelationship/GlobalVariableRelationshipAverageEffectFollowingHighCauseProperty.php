<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\VariableValueTraits\EffectDailyVariableValueTrait;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseAverageEffectFollowingHighCauseProperty;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipAverageEffectFollowingHighCauseProperty extends BaseAverageEffectFollowingHighCauseProperty
{
    use GlobalVariableRelationshipProperty, EffectDailyVariableValueTrait;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    /**
     * @param QMGlobalVariableRelationship|GlobalVariableRelationship $model
     * @return float
     */
    public static function calculate($model): float{
        $val = $model->weightedAvgFromUserVariableRelationships(static::NAME);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
