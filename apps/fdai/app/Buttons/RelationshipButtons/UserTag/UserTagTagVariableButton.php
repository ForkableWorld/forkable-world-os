<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserTag;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserTag;
use App\Models\Variable;
class UserTagTagVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = UserTag::FIELD_TAG_VARIABLE_ID;
	public $qualifiedForeignKeyName = UserTag::TABLE . '.' . UserTag::FIELD_TAG_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = UserTag::class;
	public $parentClass = UserTag::class;
	public $qualifiedParentKeyName = UserTag::TABLE . '.' . UserTag::FIELD_ID;
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
