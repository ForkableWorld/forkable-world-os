<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connector;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connector;
use App\Models\WpPost;
class ConnectorPostButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Connector::FIELD_WP_POST_ID;
	public $qualifiedForeignKeyName = Connector::TABLE . '.' . Connector::FIELD_WP_POST_ID;
	public $ownerKeyName = WpPost::FIELD_ID;
	public $qualifiedOwnerKeyName = WpPost::TABLE . '.' . WpPost::FIELD_ID;
	public $childClass = Connector::class;
	public $parentClass = Connector::class;
	public $qualifiedParentKeyName = Connector::TABLE . '.' . Connector::FIELD_ID;
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
