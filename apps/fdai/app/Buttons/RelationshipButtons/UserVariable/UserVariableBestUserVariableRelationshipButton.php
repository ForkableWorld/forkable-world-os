<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserVariableRelationship;
use App\Models\UserVariable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class UserVariableBestUserVariableRelationshipButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID;
	public $qualifiedForeignKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_BEST_USER_VARIABLE_RELATIONSHIP_ID;
	public $ownerKeyName = UserVariableRelationship::FIELD_ID;
	public $qualifiedOwnerKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_ID;
	public $childClass = UserVariable::class;
	public $parentClass = UserVariable::class;
	public $qualifiedParentKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $relatedClass = UserVariableRelationship::class;
	public $methodName = 'best_user_variable_relationship';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME;
	public $id = 'best-user-variable-relationship-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Best User Variable Relationship';
	public $title = 'Best User Variable Relationship';
	public $tooltip = UserVariableRelationship::CLASS_DESCRIPTION;
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
}
