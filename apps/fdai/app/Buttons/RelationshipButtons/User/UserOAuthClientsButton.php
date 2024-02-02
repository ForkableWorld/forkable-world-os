<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\User;
class UserOAuthClientsButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = User::TABLE . '.' . User::FIELD_CLIENT_ID;
	public $relatedClass = OAClient::class;
	public $methodName = OAClient::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = OAClient::COLOR;
	public $fontAwesome = OAClient::FONT_AWESOME;
	public $id = 'oauth-clients-button';
	public $image = OAClient::DEFAULT_IMAGE;
	public $text = 'OAuth Clients';
	public $title = 'OAuth Clients';
	public $tooltip = OAClient::CLASS_DESCRIPTION;
}
