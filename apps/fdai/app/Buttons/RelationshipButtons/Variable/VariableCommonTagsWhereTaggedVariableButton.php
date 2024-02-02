<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\CommonTag;
use App\Models\Variable;
class VariableCommonTagsWhereTaggedVariableButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = CommonTag::class;
	public $methodName = 'common_tags_where_tagged_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = CommonTag::COLOR;
	public $fontAwesome = CommonTag::FONT_AWESOME;
	public $id = 'common-tags-where-tagged-variable-button';
	public $image = CommonTag::DEFAULT_IMAGE;
	public $text = 'Common Tags Where Tagged Variable';
	public $title = 'Common Tags Where Tagged Variable';
	public $tooltip = 'Common Tags where this is the Tagged Variable';
}
