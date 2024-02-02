<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
use App\Models\UserTag;
class UserUserTagsButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = UserTag::class;
	public $methodName = UserTag::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserTag::COLOR;
	public $fontAwesome = UserTag::FONT_AWESOME;
	public $id = 'user-tags-button';
	public $image = UserTag::DEFAULT_IMAGE;
	public $text = 'User Tags';
	public $title = 'User Tags';
	public $tooltip = UserTag::CLASS_DESCRIPTION;
}
