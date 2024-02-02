<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\ButtonClick;
use App\Models\User;
class UserButtonClicksButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = ButtonClick::class;
	public $methodName = ButtonClick::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = ButtonClick::COLOR;
	public $fontAwesome = ButtonClick::FONT_AWESOME;
	public $id = 'button-clicks';
	public $image = ButtonClick::DEFAULT_IMAGE;
	public $text = 'Button Clicks';
	public $title = 'Button Clicks';
	public $tooltip = ButtonClick::CLASS_DESCRIPTION;
}
