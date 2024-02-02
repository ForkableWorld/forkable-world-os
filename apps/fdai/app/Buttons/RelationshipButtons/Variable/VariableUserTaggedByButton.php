<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\UserTag;
use App\Models\Variable;
class VariableUserTaggedByButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = UserTag::class;
	public $methodName = 'user_tagged_by';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserTag::COLOR;
	public $fontAwesome = UserTag::FONT_AWESOME;
	public $id = 'user-tagged-by-button';
	public $image = UserTag::DEFAULT_IMAGE;
	public $text = 'User Tagged By';
	public $title = 'User Tagged By';
	public $tooltip = UserTag::CLASS_DESCRIPTION;
}
