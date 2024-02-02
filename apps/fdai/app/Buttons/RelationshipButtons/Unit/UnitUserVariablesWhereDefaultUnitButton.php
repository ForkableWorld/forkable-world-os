<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Unit;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Unit;
use App\Models\UserVariable;
class UnitUserVariablesWhereDefaultUnitButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Unit::class;
	public $qualifiedParentKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = 'user_variables_where_default_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'user-variables-where-default-unit-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'User Variables Where Default Unit';
	public $title = 'User Variables Where Default Unit';
	public $tooltip = 'User Variables where this is the Default Unit';
}
