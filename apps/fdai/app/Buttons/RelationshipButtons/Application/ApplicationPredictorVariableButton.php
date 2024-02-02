<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Application;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Application;
use App\Models\Variable;
class ApplicationPredictorVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Application::FIELD_PREDICTOR_VARIABLE_ID;
	public $qualifiedForeignKeyName = Application::TABLE . '.' . Application::FIELD_PREDICTOR_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = Application::class;
	public $parentClass = Application::class;
	public $qualifiedParentKeyName = Application::TABLE . '.' . Application::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'predictor_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'predictor-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Predictor Variable';
	public $title = 'Predictor Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
