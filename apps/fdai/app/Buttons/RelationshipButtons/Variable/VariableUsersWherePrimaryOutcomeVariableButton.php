<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
use App\Models\Variable;
class VariableUsersWherePrimaryOutcomeVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = 'ID';
	public $relatedClass = User::class;
	public $methodName = 'users_where_primary_outcome_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'users-where-primary-outcome-variable-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $text = 'Users Where Primary Outcome Variable';
	public $title = 'Users Where Primary Outcome Variable';
	public $tooltip = 'Users where this is the Primary Outcome Variable';
}
