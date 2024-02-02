<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Card;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Card;
use App\Models\User;
class CardUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Card::FIELD_USER_ID;
	public $qualifiedForeignKeyName = Card::TABLE . '.' . Card::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Card::class;
	public $parentClass = Card::class;
	public $qualifiedParentKeyName = Card::TABLE . '.' . Card::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = Card::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
