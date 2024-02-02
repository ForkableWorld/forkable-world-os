<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpPost;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\VariableCategory;
use App\Models\WpPost;
class WpPostVariableCategoriesButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = WpPost::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = VariableCategory::class;
	public $methodName = VariableCategory::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = VariableCategory::COLOR;
	public $fontAwesome = VariableCategory::FONT_AWESOME;
	public $id = 'variable-categories-button';
	public $image = VariableCategory::DEFAULT_IMAGE;
	public $text = 'Variable Categories';
	public $title = 'Variable Categories';
	public $tooltip = VariableCategory::CLASS_DESCRIPTION;
}
