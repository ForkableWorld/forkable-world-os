<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariableClient;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\User;
use App\Models\UserVariableClient;
class UserVariableClientUserButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = UserVariableClient::FIELD_USER_ID;
	public $qualifiedForeignKeyName = UserVariableClient::TABLE.'.'.UserVariableClient::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE.'.'.User::FIELD_ID;
	public $childClass = UserVariableClient::class;
	public $parentClass = UserVariableClient::class;
	public $qualifiedParentKeyName = UserVariableClient::TABLE.'.'.UserVariableClient::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = UserVariableClient::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;

}
