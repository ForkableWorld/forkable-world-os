<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Application;
use App\Models\Variable;
class VariableApplicationsWhereOutcomeVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = Application::class;
	public $methodName = 'applications_where_outcome_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Application::COLOR;
	public $fontAwesome = Application::FONT_AWESOME;
	public $id = 'applications-where-outcome-variable-button';
	public $image = Application::DEFAULT_IMAGE;
	public $text = 'Applications Where Outcome Variable';
	public $title = 'Applications Where Outcome Variable';
	public $tooltip = 'Applications where this is the Outcome Variable';
}
