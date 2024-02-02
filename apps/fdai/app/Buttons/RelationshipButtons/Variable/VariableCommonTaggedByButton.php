<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\CommonTag;
use App\Models\Variable;
class VariableCommonTaggedByButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = CommonTag::class;
	public $methodName = 'common_tagged_by';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = CommonTag::COLOR;
	public $fontAwesome = CommonTag::FONT_AWESOME;
	public $id = 'common-tagged-by-button';
	public $image = CommonTag::DEFAULT_IMAGE;
	public $text = 'Common Tagged By';
	public $title = 'Common Tagged By';
	public $tooltip = CommonTag::CLASS_DESCRIPTION;
}
