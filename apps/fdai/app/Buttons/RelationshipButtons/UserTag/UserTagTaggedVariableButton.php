<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserTag;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserTag;
use App\Models\Variable;
class UserTagTaggedVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = UserTag::FIELD_TAGGED_VARIABLE_ID;
	public $qualifiedForeignKeyName = UserTag::TABLE . '.' . UserTag::FIELD_TAGGED_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = UserTag::class;
	public $parentClass = UserTag::class;
	public $qualifiedParentKeyName = UserTag::TABLE . '.' . UserTag::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'tagged_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'tagged-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Tagged Variable';
	public $title = 'Tagged Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
