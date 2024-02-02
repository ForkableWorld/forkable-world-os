<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\VariableCategory;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\UserVariable;
use App\Models\VariableCategory;
class VariableCategoryUserVariablesButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = VariableCategory::class;
	public $qualifiedParentKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = UserVariable::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'user-variables-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'User Variables';
	public $title = 'User Variables';
	public $tooltip = UserVariable::CLASS_DESCRIPTION;
}
