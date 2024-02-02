<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpTermRelationship;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\WpPost;
use App\Models\WpTermRelationship;
class WpTermRelationshipPostButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = WpTermRelationship::FIELD_OBJECT_ID;
	public $qualifiedForeignKeyName = WpTermRelationship::TABLE.'.'.WpTermRelationship::FIELD_OBJECT_ID;
	public $ownerKeyName = WpPost::FIELD_ID;
	public $qualifiedOwnerKeyName = WpPost::TABLE.'.'.WpPost::FIELD_ID;
	public $childClass = WpTermRelationship::class;
	public $parentClass = WpTermRelationship::class;
	public $qualifiedParentKeyName = 'id';
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
