<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpPost;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\WpPost;
use App\Models\WpTermRelationship;
class WpPostTermRelationshipsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = WpPost::class;
	public $qualifiedParentKeyName = 'term_taxonomy_id';
	public $relatedClass = WpTermRelationship::class;
	public $methodName = WpTermRelationship::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = WpTermRelationship::COLOR;
	public $fontAwesome = WpTermRelationship::FONT_AWESOME;
	public $id = 'term-relationships-button';
	public $image = WpTermRelationship::DEFAULT_IMAGE;
	public $text = 'Term Relationships';
	public $title = 'Term Relationships';
	public $tooltip = WpTermRelationship::CLASS_DESCRIPTION;
}
