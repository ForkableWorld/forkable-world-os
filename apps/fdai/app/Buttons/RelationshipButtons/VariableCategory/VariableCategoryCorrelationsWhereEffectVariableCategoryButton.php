<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\VariableCategory;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\UserVariableRelationship;
use App\Models\VariableCategory;
class VariableCategoryCorrelationsWhereEffectVariableCategoryButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = VariableCategory::class;
	public $qualifiedParentKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $relatedClass = UserVariableRelationship::class;
	public $methodName = 'correlations_where_effect_variable_category';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME;
	public $id = 'user_variable_relationships-where-effect-variable-category-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $text = 'VariableRelationships Where Effect Variable Category';
	public $title = 'VariableRelationships Where Effect Variable Category';
	public $tooltip = 'VariableRelationships where this is the Effect Variable Category';
}
