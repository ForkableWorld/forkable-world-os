<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\DeviceToken;
use App\Models\OAClient;
class OAClientDeviceTokensButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = DeviceToken::class;
	public $methodName = DeviceToken::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = DeviceToken::COLOR;
	public $fontAwesome = DeviceToken::FONT_AWESOME;
	public $id = 'device-tokens-button';
	public $image = DeviceToken::DEFAULT_IMAGE;
	public $text = 'Device Tokens';
	public $title = 'Device Tokens';
	public $tooltip = DeviceToken::CLASS_DESCRIPTION;
}
