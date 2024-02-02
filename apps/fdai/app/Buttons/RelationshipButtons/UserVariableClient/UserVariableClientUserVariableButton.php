<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariableClient;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserVariable;
use App\Models\UserVariableClient;
class UserVariableClientUserVariableButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = UserVariableClient::FIELD_USER_VARIABLE_ID;
	public $qualifiedForeignKeyName = UserVariableClient::TABLE.'.'.UserVariableClient::FIELD_USER_VARIABLE_ID;
	public $ownerKeyName = UserVariable::FIELD_ID;
	public $qualifiedOwnerKeyName = UserVariable::TABLE.'.'.UserVariable::FIELD_ID;
	public $childClass = UserVariableClient::class;
	public $parentClass = UserVariableClient::class;
	public $qualifiedParentKeyName = UserVariableClient::TABLE.'.'.UserVariableClient::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = 'user_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'user-variable-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'User Variable';
	public $title = 'User Variable';
	public $tooltip = UserVariable::CLASS_DESCRIPTION;

}
