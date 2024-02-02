<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
use App\Models\WpPost;
class UserPostsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $relatedClass = WpPost::class;
	public $methodName = WpPost::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = WpPost::COLOR;
	public $fontAwesome = WpPost::FONT_AWESOME;
	public $id = 'posts-button';
	public $image = WpPost::DEFAULT_IMAGE;
	public $text = 'Posts';
	public $title = 'Posts';
	public $tooltip = WpPost::CLASS_DESCRIPTION;
}
