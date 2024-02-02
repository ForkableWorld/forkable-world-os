<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpPost;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\WpPost;
use App\Models\WpPostmetum;
class WpPostPostmetaButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = WpPost::class;
	public $qualifiedParentKeyName = 'meta_id';
	public $relatedClass = WpPostmetum::class;
	public $methodName = WpPostmetum::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = WpPostmetum::COLOR;
	public $fontAwesome = WpPostmetum::FONT_AWESOME;
	public $id = 'postmeta-button';
	public $image = WpPostmetum::DEFAULT_IMAGE;
	public $text = 'Postmeta';
	public $title = 'Postmeta';
	public $tooltip = WpPostmetum::CLASS_DESCRIPTION;
}
