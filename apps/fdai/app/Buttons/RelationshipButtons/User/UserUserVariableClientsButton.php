<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
use App\Models\UserVariableClient;
class UserUserVariableClientsButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = UserVariableClient::class;
	public $methodName = UserVariableClient::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariableClient::COLOR;
	public $fontAwesome = UserVariableClient::FONT_AWESOME;
	public $id = 'user-variable-clients-button';
	public $image = UserVariableClient::DEFAULT_IMAGE;
	public $text = 'User Variable Clients';
	public $title = 'User Variable Clients';
	public $tooltip = UserVariableClient::CLASS_DESCRIPTION;
}
