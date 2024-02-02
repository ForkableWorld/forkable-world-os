<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Button;
use App\Models\OAClient;
class OAClientButtonsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
