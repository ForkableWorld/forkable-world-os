<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use \App\Traits\PropertyTraits\VariableCategoryProperty;
use App\Properties\Base\BaseSynonymsProperty;
class VariableCategorySynonymsProperty extends BaseSynonymsProperty
{
    use VariableCategoryProperty;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
    public function setFromHardCodedValue($hardCoded){
        if($hardCoded !== null){
            $this->setAttributeIfDifferentFromAccessor($hardCoded);
        }
    }
}
