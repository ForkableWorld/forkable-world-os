<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseDurationOfActionProperty;
use App\Traits\PropertyTraits\IsAverageOfCorrelations;
use App\Traits\PropertyTraits\IsCalculated;
class GlobalVariableRelationshipDurationOfActionProperty extends BaseDurationOfActionProperty
{
    use GlobalVariableRelationshipProperty;
    use IsCalculated;
    use IsAverageOfCorrelations;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public $canBeChangedToNull = false;
    public $required = true;
}
