<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Purchase;
use App\Models\User;
class UserPurchasesWhereSubscriberUserButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = Purchase::class;
	public $methodName = 'purchases_where_subscriber_user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Purchase::COLOR;
	public $fontAwesome = Purchase::FONT_AWESOME;
	public $id = 'purchases-where-subscriber-user-button';
	public $image = Purchase::DEFAULT_IMAGE;
	public $text = 'Purchases Where Subscriber User';
	public $title = 'Purchases Where Subscriber User';
	public $tooltip = 'Purchases where this is the Subscriber User';
}
