<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
use App\Models\WpUsermetum;
class UserUsermetaButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'umeta_id';
	public $relatedClass = WpUsermetum::class;
	public $methodName = 'usermeta';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = WpUsermetum::COLOR;
	public $fontAwesome = WpUsermetum::FONT_AWESOME;
	public $id = 'usermeta-button';
	public $image = WpUsermetum::DEFAULT_IMAGE;
	public $text = 'Usermeta';
	public $title = 'Usermeta';
	public $tooltip = WpUsermetum::CLASS_DESCRIPTION;
}
