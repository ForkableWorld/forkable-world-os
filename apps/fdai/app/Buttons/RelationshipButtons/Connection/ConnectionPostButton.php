<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connection;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connection;
use App\Models\WpPost;
class ConnectionPostButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Connection::FIELD_WP_POST_ID;
	public $qualifiedForeignKeyName = Connection::TABLE . '.' . Connection::FIELD_WP_POST_ID;
	public $ownerKeyName = WpPost::FIELD_ID;
	public $qualifiedOwnerKeyName = WpPost::TABLE . '.' . WpPost::FIELD_ID;
	public $childClass = Connection::class;
	public $parentClass = Connection::class;
	public $qualifiedParentKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
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
