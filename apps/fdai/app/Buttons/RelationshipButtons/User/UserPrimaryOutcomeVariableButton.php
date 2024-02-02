<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\User;
use App\Models\Variable;
class UserPrimaryOutcomeVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID;
	public $qualifiedForeignKeyName = User::TABLE . '.' . User::FIELD_PRIMARY_OUTCOME_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = User::class;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'primary_outcome_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'primary-outcome-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Primary Outcome Variable';
	public $title = 'Primary Outcome Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
