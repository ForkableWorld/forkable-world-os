<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
use App\Models\WpLink;
class UserLinksButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'link_id';
	public $relatedClass = WpLink::class;
	public $methodName = WpLink::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = WpLink::COLOR;
	public $fontAwesome = WpLink::FONT_AWESOME;
	public $id = 'links-button';
	public $image = WpLink::DEFAULT_IMAGE;
	public $text = 'Links';
	public $title = 'Links';
	public $tooltip = WpLink::CLASS_DESCRIPTION;
}
