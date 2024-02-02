<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Properties\Base\BasePredictsHighEffectChangeProperty;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Traits\PropertyTraits\IsAverageOfCorrelations;
use App\Traits\PropertyTraits\IsCalculated;
class GlobalVariableRelationshipPredictsHighEffectChangeProperty extends BasePredictsHighEffectChangeProperty {
	use GlobalVariableRelationshipProperty, IsAverageOfCorrelations;
	use IsCalculated;
	public $parentClass = GlobalVariableRelationship::class;
	public $table = GlobalVariableRelationship::TABLE;
}
