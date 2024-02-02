<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Study;
use App\Models\Variable;
class VariableStudiesWhereCauseVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = Study::class;
	public $methodName = 'studies_where_cause_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Study::COLOR;
	public $fontAwesome = Study::FONT_AWESOME;
	public $id = 'studies-where-cause-variable-button';
	public $image = Study::DEFAULT_IMAGE;
	public $text = 'Studies Where Cause Variable';
	public $title = 'Studies Where Cause Variable';
	public $tooltip = 'Studies where this is the Cause Variable';
}
