<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\VariableCategory;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Unit;
use App\Models\VariableCategory;
class VariableCategoryDefaultUnitButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = VariableCategory::FIELD_DEFAULT_UNIT_ID;
	public $qualifiedForeignKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_DEFAULT_UNIT_ID;
	public $ownerKeyName = Unit::FIELD_ID;
	public $qualifiedOwnerKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $childClass = VariableCategory::class;
	public $parentClass = VariableCategory::class;
	public $qualifiedParentKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $relatedClass = Unit::class;
	public $methodName = 'default_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Unit::COLOR;
	public $fontAwesome = Unit::FONT_AWESOME;
	public $id = 'default-unit-button';
	public $image = Unit::DEFAULT_IMAGE;
	public $text = 'Default Unit';
	public $title = 'Default Unit';
	public $tooltip = Unit::CLASS_DESCRIPTION;
}
