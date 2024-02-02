<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Unit;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Unit;
use App\Models\Variable;
class UnitVariablesWhereDefaultUnitButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Unit::class;
	public $qualifiedParentKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'variables_where_default_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'variables-where-default-unit-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Variables Where Default Unit';
	public $title = 'Variables Where Default Unit';
	public $tooltip = 'Variables where this is the Default Unit';
}
