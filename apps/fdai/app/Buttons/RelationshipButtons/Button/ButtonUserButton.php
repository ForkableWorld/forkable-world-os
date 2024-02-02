<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Button;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Button;
use App\Models\User;
class ButtonUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Button::FIELD_USER_ID;
	public $qualifiedForeignKeyName = Button::TABLE . '.' . Button::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Button::class;
	public $parentClass = Button::class;
	public $qualifiedParentKeyName = Button::TABLE . '.' . Button::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = Button::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
