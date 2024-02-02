<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\UserVariableClient;
class OAClientUserVariableClientsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
