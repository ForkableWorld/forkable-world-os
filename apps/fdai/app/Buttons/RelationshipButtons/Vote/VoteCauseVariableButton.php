<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Vote;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Variable;
use App\Models\Vote;
class VoteCauseVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Vote::FIELD_CAUSE_VARIABLE_ID;
	public $qualifiedForeignKeyName = Vote::TABLE . '.' . Vote::FIELD_CAUSE_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = Vote::class;
	public $parentClass = Vote::class;
	public $qualifiedParentKeyName = Vote::TABLE . '.' . Vote::FIELD_ID;
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
