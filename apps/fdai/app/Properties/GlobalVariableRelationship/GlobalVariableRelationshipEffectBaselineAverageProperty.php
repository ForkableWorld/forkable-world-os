<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseEffectBaselineAverageProperty;
use App\Traits\PropertyTraits\IsAverageOfCorrelations;
class GlobalVariableRelationshipEffectBaselineAverageProperty extends BaseEffectBaselineAverageProperty
{
    use GlobalVariableRelationshipProperty, IsAverageOfCorrelations;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    use \App\Traits\PropertyTraits\IsCalculated;
}
