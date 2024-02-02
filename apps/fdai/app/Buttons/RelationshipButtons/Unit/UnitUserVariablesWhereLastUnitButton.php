<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Unit;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Unit;
use App\Models\UserVariable;
class UnitUserVariablesWhereLastUnitButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Unit::class;
	public $qualifiedParentKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = 'user_variables_where_last_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'user-variables-where-last-unit-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'User Variables Where Last Unit';
	public $title = 'User Variables Where Last Unit';
	public $tooltip = 'User Variables where this is the Last Unit';
}
