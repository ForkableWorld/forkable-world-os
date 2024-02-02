<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\UserClient;
class OAClientUserClientsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
