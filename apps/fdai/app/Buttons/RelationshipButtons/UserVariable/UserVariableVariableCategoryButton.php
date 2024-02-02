<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\UserVariable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserVariable;
use App\Models\VariableCategory;
class UserVariableVariableCategoryButton extends BelongsToRelationshipButton {
	public $foreignKeyName = UserVariable::FIELD_VARIABLE_CATEGORY_ID;
	public $qualifiedForeignKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_VARIABLE_CATEGORY_ID;
	public $ownerKeyName = VariableCategory::FIELD_ID;
	public $qualifiedOwnerKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $childClass = UserVariable::class;
	public $parentClass = UserVariable::class;
	public $qualifiedParentKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $relatedClass = VariableCategory::class;
	public $methodName = 'variable_category';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = VariableCategory::COLOR;
	public $fontAwesome = VariableCategory::FONT_AWESOME;
	public $id = 'variable-category-button';
	public $image = VariableCategory::DEFAULT_IMAGE;
	public $text = 'User-Defined Variable Category';
	public $title = 'User-Defined Variable Category';
	public $tooltip = "User defined variable category ";
}
