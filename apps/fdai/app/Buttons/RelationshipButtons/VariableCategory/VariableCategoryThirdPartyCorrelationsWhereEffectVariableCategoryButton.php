<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\VariableCategory;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\ThirdPartyCorrelation;
use App\Models\VariableCategory;
class VariableCategoryThirdPartyCorrelationsWhereEffectVariableCategoryButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = VariableCategory::class;
	public $qualifiedParentKeyName = VariableCategory::TABLE . '.' . VariableCategory::FIELD_ID;
	public $relatedClass = ThirdPartyCorrelation::class;
	public $methodName = 'third_party_correlations_where_effect_variable_category';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = ThirdPartyCorrelation::COLOR;
	public $fontAwesome = ThirdPartyCorrelation::FONT_AWESOME;
	public $id = 'third-party-user_variable_relationships-where-effect-variable-category-button';
	public $image = ThirdPartyCorrelation::DEFAULT_IMAGE;
	public $text = 'Third Party VariableRelationships Where Effect Variable Category';
	public $title = 'Third Party VariableRelationships Where Effect Variable Category';
	public $tooltip = 'Third Party VariableRelationships where this is the Effect Variable Category';
}
