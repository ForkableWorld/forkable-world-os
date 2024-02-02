<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Subscription;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Subscription;
use App\Models\User;
class SubscriptionUserButton extends BelongsToRelationshipButton {
	public $foreignKeyName = Subscription::FIELD_USER_ID;
	public $qualifiedForeignKeyName = Subscription::TABLE . '.' . Subscription::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Subscription::class;
	public $parentClass = Subscription::class;
	public $qualifiedParentKeyName = Subscription::TABLE . '.' . Subscription::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = Subscription::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
