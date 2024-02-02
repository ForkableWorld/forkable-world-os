<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Unit;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Unit;
use App\Models\VariableCategory;
class UnitVariableCategoriesWhereDefaultUnitButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Unit::class;
	public $qualifiedParentKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $relatedClass = VariableCategory::class;
	public $methodName = 'variable_categories_where_default_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = VariableCategory::COLOR;
	public $fontAwesome = VariableCategory::FONT_AWESOME;
	public $id = 'variable-categories-where-default-unit-button';
	public $image = VariableCategory::DEFAULT_IMAGE;
	public $text = 'Variable Categories Where Default Unit';
	public $title = 'Variable Categories Where Default Unit';
	public $tooltip = 'Variable Categories where this is the Default Unit';
}
