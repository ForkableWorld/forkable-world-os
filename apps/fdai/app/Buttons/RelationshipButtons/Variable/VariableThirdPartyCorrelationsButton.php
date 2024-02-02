<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\ThirdPartyCorrelation;
use App\Models\Variable;
class VariableThirdPartyCorrelationsButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = ThirdPartyCorrelation::class;
	public $methodName = ThirdPartyCorrelation::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = ThirdPartyCorrelation::COLOR;
	public $fontAwesome = ThirdPartyCorrelation::FONT_AWESOME;
	public $id = 'third-party-user_variable_relationships-button';
	public $image = ThirdPartyCorrelation::DEFAULT_IMAGE;
	public $text = 'Third Party VariableRelationships';
	public $title = 'Third Party VariableRelationships';
	public $tooltip = ThirdPartyCorrelation::CLASS_DESCRIPTION;
}
