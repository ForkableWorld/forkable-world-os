<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpTermTaxonomy;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\WpTerm;
use App\Models\WpTermTaxonomy;
class WpTermTaxonomyTermButton extends BelongsToRelationshipButton {
	public $foreignKeyName = WpTerm::FIELD_TERM_ID;
	public $qualifiedForeignKeyName = WpTermTaxonomy::TABLE . '.' . WpTermTaxonomy::FIELD_TERM_ID;
	public $ownerKeyName = WpTerm::FIELD_TERM_ID;
	public $qualifiedOwnerKeyName = WpTerm::TABLE . '.' . WpTerm::FIELD_TERM_ID;
	public $childClass = WpTermTaxonomy::class;
	public $parentClass = WpTermTaxonomy::class;
	public $qualifiedParentKeyName = WpTermTaxonomy::TABLE . '.' . WpTermTaxonomy::FIELD_TERM_TAXONOMY_ID;
	public $relatedClass = WpTerm::class;
	public $methodName = 'wp_term';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = WpTerm::COLOR;
	public $fontAwesome = WpTerm::FONT_AWESOME;
	public $id = 'term-button';
	public $image = WpTerm::DEFAULT_IMAGE;
	public $text = 'Term';
	public $title = 'Term';
	public $tooltip = WpTerm::CLASS_DESCRIPTION;
}
