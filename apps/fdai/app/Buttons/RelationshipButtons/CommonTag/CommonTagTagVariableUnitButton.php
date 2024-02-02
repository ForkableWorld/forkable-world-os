<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\RelationshipButtons\CommonTag;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\CommonTag;
use App\Models\Unit;
class CommonTagTagVariableUnitButton extends BelongsToRelationshipButton {
	public $foreignKeyName = CommonTag::FIELD_TAG_VARIABLE_UNIT_ID;
	public $qualifiedForeignKeyName = CommonTag::TABLE . '.' . CommonTag::FIELD_TAG_VARIABLE_UNIT_ID;
	public $ownerKeyName = Unit::FIELD_ID;
	public $qualifiedOwnerKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $childClass = CommonTag::class;
	public $parentClass = CommonTag::class;
	public $qualifiedParentKeyName = CommonTag::TABLE . '.' . CommonTag::FIELD_ID;
	public $relatedClass = Unit::class;
	public $methodName = 'tag_variable_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Unit::COLOR;
	public $fontAwesome = Unit::FONT_AWESOME;
	public $id = 'tag-variable-unit-button';
	public $image = Unit::DEFAULT_IMAGE;
	public $text = 'Tag Variable Unit';
	public $title = 'Tag Variable Unit';
	public $tooltip = Unit::CLASS_DESCRIPTION;
}
