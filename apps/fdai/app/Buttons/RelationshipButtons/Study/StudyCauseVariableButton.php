<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Study;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Study;
use App\Models\Variable;
class StudyCauseVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Study::FIELD_CAUSE_VARIABLE_ID;
	public $qualifiedForeignKeyName = Study::TABLE . '.' . Study::FIELD_CAUSE_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = Study::class;
	public $parentClass = Study::class;
	public $qualifiedParentKeyName = Study::TABLE . '.' . Study::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'cause_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'cause-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Cause Variable';
	public $title = 'Cause Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
