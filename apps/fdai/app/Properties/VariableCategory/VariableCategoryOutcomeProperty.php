<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Traits\PropertyTraits\VariableCategoryProperty;
use App\Properties\Base\BaseOutcomeProperty;
class VariableCategoryOutcomeProperty extends BaseOutcomeProperty
{
    use VariableCategoryProperty;
    // Generally leave this null so it can be set at the variable level.
    // Keystrokes are activities and it may be a goal for keystrokes to be higher.
    // Also, maybe people have a goal of engaging (or their kids) in certain activities more
    public $description = "Keep this null unless you want to overwrite every single variable outcome value. ";
    public $canBeChangedToNull = true;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
}
