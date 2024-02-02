<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Properties\Base\BaseStatusProperty;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
class GlobalVariableRelationshipStatusProperty extends BaseStatusProperty
{
    use GlobalVariableRelationshipProperty;

    const STATUS_WAITING = 'WAITING';
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public $enum = [
        CorrelationStatusProperty::STATUS_UPDATED,
        CorrelationStatusProperty::STATUS_ANALYZING,
        CorrelationStatusProperty::STATUS_ERROR,
        CorrelationStatusProperty::STATUS_WAITING,
    ];
    protected function isLowerCase():bool{return false;}
	public function getEnumOptions(): array{
		return $this->enum;
	}
}
