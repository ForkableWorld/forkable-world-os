<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
use App\Models\UserClient;
class UserUserClientsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = UserClient::class;
	public $methodName = UserClient::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserClient::COLOR;
	public $fontAwesome = UserClient::FONT_AWESOME;
	public $id = 'user-clients-button';
	public $image = UserClient::DEFAULT_IMAGE;
	public $text = 'User Clients';
	public $title = 'User Clients';
	public $tooltip = UserClient::CLASS_DESCRIPTION;
}
