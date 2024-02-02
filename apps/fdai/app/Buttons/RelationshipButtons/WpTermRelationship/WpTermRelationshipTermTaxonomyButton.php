<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpTermRelationship;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\WpTermTaxonomy;
use App\Models\WpTermRelationship;
class WpTermRelationshipTermTaxonomyButton extends BelongsToRelationshipButton {
	public $foreignKeyName = WpTermTaxonomy::FIELD_TERM_TAXONOMY_ID;
	public $qualifiedForeignKeyName = WpTermRelationship::TABLE.'.'.WpTermRelationship::FIELD_ID;
	public $ownerKeyName = WpTermTaxonomy::FIELD_TERM_TAXONOMY_ID;
	public $qualifiedOwnerKeyName = WpTermTaxonomy::TABLE.'.'.WpTermTaxonomy::FIELD_TERM_TAXONOMY_ID;
	public $childClass = WpTermRelationship::class;
	public $parentClass = WpTermRelationship::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = WpTermTaxonomy::class;
	public $methodName = WpTermTaxonomy::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = WpTermTaxonomy::COLOR;
	public $fontAwesome = WpTermTaxonomy::FONT_AWESOME;
	public $id = 'term-taxonomy-button';
	public $image = WpTermTaxonomy::DEFAULT_IMAGE;
	public $text = 'Term Taxonomy';
	public $title = 'Term Taxonomy';
	public $tooltip = WpTermTaxonomy::CLASS_DESCRIPTION;

}
