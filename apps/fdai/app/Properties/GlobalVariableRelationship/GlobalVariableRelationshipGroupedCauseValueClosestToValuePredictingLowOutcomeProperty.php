<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\VariableValueTraits\CauseAggregatedVariableValueTrait;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseGroupedCauseValueClosestToValuePredictingLowOutcomeProperty;
use App\Traits\PropertyTraits\IsAverageOfCorrelations;
use App\VariableRelationships\QMGlobalVariableRelationship;
class GlobalVariableRelationshipGroupedCauseValueClosestToValuePredictingLowOutcomeProperty extends BaseGroupedCauseValueClosestToValuePredictingLowOutcomeProperty
{
    use GlobalVariableRelationshipProperty, CauseAggregatedVariableValueTrait, IsAverageOfCorrelations;
    use \App\Traits\PropertyTraits\IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
}
