<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Unit;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\CommonTag;
use App\Models\Unit;
class UnitCommonTagsWhereTaggedVariableUnitButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Unit::class;
	public $qualifiedParentKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $relatedClass = CommonTag::class;
	public $methodName = 'common_tags_where_tagged_variable_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = CommonTag::COLOR;
	public $fontAwesome = CommonTag::FONT_AWESOME;
	public $id = 'common-tags-where-tagged-variable-unit-button';
	public $image = CommonTag::DEFAULT_IMAGE;
	public $text = 'Common Tags Where Tagged Variable Unit';
	public $title = 'Common Tags Where Tagged Variable Unit';
	public $tooltip = 'Common Tags where this is the Tagged Variable Unit';
}
