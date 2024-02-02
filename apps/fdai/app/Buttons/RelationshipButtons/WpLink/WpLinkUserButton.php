<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpLink;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\User;
use App\Models\WpLink;
class WpLinkUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = WpLink::FIELD_LINK_OWNER;
	public $qualifiedForeignKeyName = WpLink::TABLE . '.' . WpLink::FIELD_LINK_OWNER;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = WpLink::class;
	public $parentClass = WpLink::class;
	public $qualifiedParentKeyName = WpLink::TABLE . '.' . WpLink::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = WpLink::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
