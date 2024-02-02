<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\GlobalVariableRelationship;
use App\Models\GlobalVariableRelationship;
use App\Traits\PropertyTraits\GlobalVariableRelationshipProperty;
use App\Properties\Base\BaseEffectVariableCategoryIdProperty;
class GlobalVariableRelationshipEffectVariableCategoryIdProperty extends BaseEffectVariableCategoryIdProperty
{
    use GlobalVariableRelationshipProperty;
    public $table = GlobalVariableRelationship::TABLE;
    public $parentClass = GlobalVariableRelationship::class;
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnIndex(): bool {return false;}
    public function showOnDetail(): bool {return true;}
    public function validate(): void {
        parent::validate();
//        $ac = $this->getGlobalVariableRelationship();
//        if($ac->number_of_correlations < 2){
//            $this->assertNotBoring(); // We can just mark these user_variable_relationships as boring in DB
//        }
    }
}
