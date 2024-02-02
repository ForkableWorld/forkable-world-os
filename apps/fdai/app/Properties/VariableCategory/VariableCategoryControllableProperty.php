<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Traits\PropertyTraits\VariableCategoryProperty;
use App\UI\FontAwesome;
use App\UI\ImageUrls;

class VariableCategoryControllableProperty extends SometimesEnumProperty
{
    use VariableCategoryProperty;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
    public $default = self::SOMETIMES;
    public $description = 'controllable';
    public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
    public $image = ImageUrls::QUESTION_MARK;
    public $name = self::NAME;
    public const NAME = 'controllable';
    public $showOnDetail = true;
    public $title = 'Controllable';
}
