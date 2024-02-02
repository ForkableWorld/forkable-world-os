<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Variable;
use App\Models\VariableCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VariableVariableCategoryButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Variable::FIELD_VARIABLE_CATEGORY_ID;
	public $qualifiedForeignKeyName = Variable::TABLE . '.' . Variable::FIELD_VARIABLE_CATEGORY_ID;
	public $ownerKeyName = VariableCategory::FIELD_ID;
	public $qualifiedOwnerKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $childClass = Variable::class;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = VariableCategory::class;
	public $methodName = 'variable_category';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = VariableCategory::COLOR;
	public $fontAwesome = VariableCategory::FONT_AWESOME;
	public $id = 'variable-category-button';
	public $image = VariableCategory::DEFAULT_IMAGE;
	public $text = 'Variable Category';
	public $title = 'Variable Category';
	public $tooltip = VariableCategory::CLASS_DESCRIPTION;
	public function __construct($methodOrModel, BelongsTo $relation = null){
		parent::__construct($methodOrModel, $relation);
	}
}
