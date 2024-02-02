<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpTerm;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\WpTerm;
use App\Models\WpTermTaxonomy;
class WpTermTermTaxonomiesButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = WpTerm::class;
	public $qualifiedParentKeyName = WpTerm::TABLE . '.' . WpTerm::FIELD_TERM_ID;
	public $relatedClass = WpTermTaxonomy::class;
	public $methodName = 'wp_term_taxonomies';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = WpTermTaxonomy::COLOR;
	public $fontAwesome = WpTermTaxonomy::FONT_AWESOME;
	public $id = 'term-taxonomies-button';
	public $image = WpTermTaxonomy::DEFAULT_IMAGE;
	public $text = 'Term Taxonomies';
	public $title = 'Term Taxonomies';
	public $tooltip = WpTermTaxonomy::CLASS_DESCRIPTION;
}
