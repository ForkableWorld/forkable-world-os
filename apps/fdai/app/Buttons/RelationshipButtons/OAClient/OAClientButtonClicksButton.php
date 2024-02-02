<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\ButtonClick;
use App\Models\OAClient;
class OAClientButtonClicksButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
