<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\DeviceToken;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\DeviceToken;
use App\Models\User;
class DeviceTokenUserButton extends BelongsToRelationshipButton {
	public $foreignKeyName = DeviceToken::FIELD_USER_ID;
	public $qualifiedForeignKeyName = DeviceToken::TABLE . '.' . DeviceToken::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = DeviceToken::class;
	public $parentClass = DeviceToken::class;
	public $qualifiedParentKeyName = DeviceToken::TABLE . '.' . DeviceToken::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
