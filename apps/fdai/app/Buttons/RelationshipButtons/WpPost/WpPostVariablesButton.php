<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\WpPost;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Variable;
use App\Models\WpPost;
class WpPostVariablesButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = WpPost::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = Variable::class;
	public $methodName = Variable::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'variables-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = Variable::CLASS_CATEGORY;
	public $title = Variable::CLASS_CATEGORY;
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
