<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Traits\PropertyTraits\VariableCategoryProperty;
use App\Properties\Base\BaseNameProperty;
class VariableCategoryNameProperty extends BaseNameProperty
{
    use VariableCategoryProperty;
    public const SUFFIX_INTAKE = 'Intake';
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
}
