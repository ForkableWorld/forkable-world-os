<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Traits\HardCodableProperty;
use App\Traits\PropertyTraits\VariableCategoryProperty;
use App\Properties\Base\BaseManualTrackingProperty;
use App\Fields\Field;
class VariableCategoryManualTrackingProperty extends BaseManualTrackingProperty
{
    use VariableCategoryProperty, HardCodableProperty;
    // Generally leave this null so it can be set at the variable level.  Rescuetime variables false manual in their models
    public $description = "Keep this null unless you want to overwrite every single variable manual tracking setting. ";
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
    public $canBeChangedToNull = true;
    public $required = false;
    public function setFromHardCodedValue($hardCoded){
        $this->setValue($hardCoded);
    }
    public function getField($resolveCallback = null, string $name = null): Field{
        return parent::getField($resolveCallback, $name);
    }
    public function getHardCodedValue(): string{
        return parent::getHardCodedValue();
    }
}
