<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Variable;
class VariableBestCauseVariableButton extends BelongsToRelationshipButton {
	public $foreignKeyName = Variable::FIELD_BEST_CAUSE_VARIABLE_ID;
	public $qualifiedForeignKeyName = Variable::TABLE . '.' . Variable::FIELD_BEST_CAUSE_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = Variable::class;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'best_cause_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'best-cause-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Best Cause Variable';
	public $title = 'Best Cause Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
