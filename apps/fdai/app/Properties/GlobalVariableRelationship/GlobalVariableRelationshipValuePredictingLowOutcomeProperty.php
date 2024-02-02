<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\VariableValueTraits\CauseDailyVariableValueTrait;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseValuePredictingLowOutcomeProperty;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipValuePredictingLowOutcomeProperty extends BaseValuePredictingLowOutcomeProperty
{
    use GlobalVariableRelationshipProperty, CauseDailyVariableValueTrait;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public const SYNONYMS = [
        'avgDailyValuePredictingLowOutcome',
    ];
    /**
     * @param QMGlobalVariableRelationship|GlobalVariableRelationship $model
     * @return float
     */
    public static function calculate($model){
        $val = $model->weightedAvgFromUserVariableRelationships(static::NAME);
        $model->setAttribute(static::NAME, $val);
        return $val;
    }
}
