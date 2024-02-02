<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Correlation;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
class CorrelationUserVariablesWhereBestUserVariableRelationshipButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = UserVariableRelationship::class;
	public $qualifiedParentKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = 'user_variables_where_best_user_variable_relationship';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'user-variables-where-best-user-variable-relationship-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'User Variables Where Best User Variable Relationship';
	public $title = 'User Variables Where Best User Variable Relationship';
	public $tooltip = 'User Variables where this is the Best User Variable Relationship';
}
