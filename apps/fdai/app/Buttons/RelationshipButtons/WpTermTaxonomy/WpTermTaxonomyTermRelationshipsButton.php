<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpTermTaxonomy;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\WpTermRelationship;
use App\Models\WpTermTaxonomy;
class WpTermTaxonomyTermRelationshipsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = WpTermTaxonomy::class;
	public $qualifiedParentKeyName = WpTermTaxonomy::TABLE . '.' . WpTermTaxonomy::FIELD_TERM_TAXONOMY_ID;
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
