<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class GlobalVariableRelationshipIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use GlobalVariableRelationshipProperty;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'global_variable_relationship_id',
        'id',
    ];
    public function validate(): void {
        parent::validate();
    }
    public function cannotBeChangedToNull(): bool{
        return parent::cannotBeChangedToNull();
    }
}
