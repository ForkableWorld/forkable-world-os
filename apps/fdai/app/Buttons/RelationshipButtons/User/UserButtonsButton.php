<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Button;
use App\Models\User;
class UserButtonsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = Button::class;
	public $methodName = Button::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Button::COLOR;
	public $fontAwesome = Button::FONT_AWESOME;
	public $id = Button::TABLE;
	public $image = Button::DEFAULT_IMAGE;
	public $text = 'Buttons';
	public $title = 'Buttons';
	public $tooltip = Button::CLASS_DESCRIPTION;
}
