<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Variable;
class VariablePopulationEffectStudiesButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = GlobalVariableRelationship::class;
	public $methodName = 'population_effect_studies';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME_EFFECTS;
	public $id = 'population-effect-studies-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Outcomes';
	public $title = 'Outcomes';
	public $tooltip = "Analyses of possible effects of this variable for the average person based on aggregated population level data. ";
}
