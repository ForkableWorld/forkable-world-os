<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Variable;
class VariableVariablesWhereBestEffectVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'variables_where_best_effect_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'variables-where-best-effect-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Variables Where Best Effect Variable';
	public $title = 'Variables Where Best Effect Variable';
	public $tooltip = 'Variables where this is the Best Effect Variable';
}
