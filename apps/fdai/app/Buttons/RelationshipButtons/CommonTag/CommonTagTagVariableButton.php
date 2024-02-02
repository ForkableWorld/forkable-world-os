<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\CommonTag;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\CommonTag;
use App\Models\Variable;
class CommonTagTagVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = CommonTag::FIELD_TAG_VARIABLE_ID;
	public $qualifiedForeignKeyName = CommonTag::TABLE . '.' . CommonTag::FIELD_TAG_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = CommonTag::class;
	public $parentClass = CommonTag::class;
	public $qualifiedParentKeyName = CommonTag::TABLE . '.' . CommonTag::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'tag_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'tag-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Tag Variable';
	public $title = 'Tag Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
