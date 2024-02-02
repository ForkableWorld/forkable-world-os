<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\UserTag;
use App\Models\Variable;
class VariableUserTagsWhereTaggedVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = UserTag::class;
	public $methodName = 'user_tags_where_tagged_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserTag::COLOR;
	public $fontAwesome = UserTag::FONT_AWESOME;
	public $id = 'user-tags-where-tagged-variable-button';
	public $image = UserTag::DEFAULT_IMAGE;
	public $text = 'User Tags Where Tagged Variable';
	public $title = 'User Tags Where Tagged Variable';
	public $tooltip = 'User Tags where this is the Tagged Variable';
}
