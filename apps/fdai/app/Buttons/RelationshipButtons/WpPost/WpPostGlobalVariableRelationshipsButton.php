<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpPost;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\GlobalVariableRelationship;
use App\Models\WpPost;
class WpPostGlobalVariableRelationshipsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = WpPost::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = GlobalVariableRelationship::class;
	public $methodName = GlobalVariableRelationship::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = GlobalVariableRelationship::FONT_AWESOME;
	public $id = 'global-variable-relationships-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Global Variable Relationships';
	public $title = 'Global Variable Relationships';
	public $tooltip = GlobalVariableRelationship::CLASS_DESCRIPTION;
}
