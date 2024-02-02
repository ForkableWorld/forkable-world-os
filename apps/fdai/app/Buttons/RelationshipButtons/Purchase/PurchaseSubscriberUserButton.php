<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\RelationshipButtons\Purchase;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Purchase;
use App\Models\User;
class PurchaseSubscriberUserButton extends BelongsToRelationshipButton {
	public $foreignKeyName = Purchase::FIELD_SUBSCRIBER_USER_ID;
	public $qualifiedForeignKeyName = Purchase::TABLE . '.' . Purchase::FIELD_SUBSCRIBER_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Purchase::class;
	public $parentClass = Purchase::class;
	public $qualifiedParentKeyName = Purchase::TABLE . '.' . Purchase::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'subscriber_user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'subscriber-user-button';
	public $image = Purchase::DEFAULT_IMAGE;
	public $text = 'Subscriber User';
	public $title = 'Subscriber User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
