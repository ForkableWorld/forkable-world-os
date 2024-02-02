<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\VariableCategory;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\UserVariableRelationship;
use App\Models\VariableCategory;
class VariableCategoryCorrelationsWhereCauseVariableCategoryButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = VariableCategory::class;
	public $qualifiedParentKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $relatedClass = UserVariableRelationship::class;
	public $methodName = 'correlations_where_cause_variable_category';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME;
	public $id = 'user_variable_relationships-where-cause-variable-category-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $text = 'VariableRelationships Where Cause Variable Category';
	public $title = 'VariableRelationships Where Cause Variable Category';
	public $tooltip = 'VariableRelationships where this is the Cause Variable Category';
}
