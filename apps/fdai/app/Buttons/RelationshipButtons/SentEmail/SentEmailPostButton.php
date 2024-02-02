<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\SentEmail;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\SentEmail;
use App\Models\WpPost;
class SentEmailPostButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = SentEmail::FIELD_WP_POST_ID;
	public $qualifiedForeignKeyName = SentEmail::TABLE . '.' . SentEmail::FIELD_WP_POST_ID;
	public $ownerKeyName = WpPost::FIELD_ID;
	public $qualifiedOwnerKeyName = WpPost::TABLE . '.' . WpPost::FIELD_ID;
	public $childClass = SentEmail::class;
	public $parentClass = SentEmail::class;
	public $qualifiedParentKeyName = SentEmail::TABLE . '.' . SentEmail::FIELD_ID;
	public $relatedClass = WpPost::class;
	public $methodName = 'wp_post';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = WpPost::COLOR;
	public $fontAwesome = WpPost::FONT_AWESOME;
	public $id = 'post-button';
	public $image = WpPost::DEFAULT_IMAGE;
	public $text = 'Post';
	public $title = 'Post';
	public $tooltip = WpPost::CLASS_DESCRIPTION;
}
