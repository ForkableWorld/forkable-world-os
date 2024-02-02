<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Traits\PropertyTraits\VariableCategoryProperty;
use App\Properties\Base\BaseIsPublicProperty;
class VariableCategoryIsPublicProperty extends BaseIsPublicProperty
{
    use VariableCategoryProperty;
    public $description = 'If is_public is set to true, the category will be visible to all users.';
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
}
