<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Http\Requests\AstralRequest;
class GlobalVariableRelationshipEffectVariableIdProperty extends BaseEffectVariableIdProperty
{
    use GlobalVariableRelationshipProperty;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {
        return AstralRequest::forRelationshipTable("global_variable_relationships_where_cause_variable");
    }
    public function showOnDetail(): bool {return true;}
}
