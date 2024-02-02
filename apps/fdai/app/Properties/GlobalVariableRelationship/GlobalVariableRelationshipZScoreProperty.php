<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseZScoreProperty;
use App\Traits\PropertyTraits\IsAverageOfCorrelations;
use App\Traits\PropertyTraits\IsCalculated;
class GlobalVariableRelationshipZScoreProperty extends BaseZScoreProperty
{
    use GlobalVariableRelationshipProperty, IsAverageOfCorrelations;
    use IsCalculated;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
}
