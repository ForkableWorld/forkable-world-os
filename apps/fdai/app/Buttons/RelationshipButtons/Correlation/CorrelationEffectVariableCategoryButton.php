<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Correlation;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserVariableRelationship;
use App\Models\VariableCategory;
class CorrelationEffectVariableCategoryButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID;
	public $qualifiedForeignKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_EFFECT_VARIABLE_CATEGORY_ID;
	public $ownerKeyName = VariableCategory::FIELD_ID;
	public $qualifiedOwnerKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $childClass = UserVariableRelationship::class;
	public $parentClass = UserVariableRelationship::class;
	public $qualifiedParentKeyName = UserVariableRelationship::TABLE . '.' . UserVariableRelationship::FIELD_ID;
	public $relatedClass = VariableCategory::class;
	public $methodName = 'effect_variable_category';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = VariableCategory::COLOR;
	public $fontAwesome = VariableCategory::FONT_AWESOME;
	public $id = 'effect-variable-category-button';
	public $image = VariableCategory::DEFAULT_IMAGE;
	public $text = 'Effect Variable Category';
	public $title = 'Effect Variable Category';
	public $tooltip = VariableCategory::CLASS_DESCRIPTION;
}
