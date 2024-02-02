<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UnitCategory;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Unit;
use App\Models\UnitCategory;
class UnitCategoryUnitsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = UnitCategory::class;
	public $qualifiedParentKeyName = UnitCategory::TABLE . '.' . UnitCategory::FIELD_ID;
	public $relatedClass = Unit::class;
	public $methodName = Unit::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Unit::COLOR;
	public $fontAwesome = Unit::FONT_AWESOME;
	public $id = 'units-button';
	public $image = Unit::DEFAULT_IMAGE;
	public $text = 'Units';
	public $title = 'Units';
	public $tooltip = Unit::CLASS_DESCRIPTION;
}
