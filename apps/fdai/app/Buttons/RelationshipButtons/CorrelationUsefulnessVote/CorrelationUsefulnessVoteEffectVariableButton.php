<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\CorrelationUsefulnessVote;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Variable;
use App\Models\CorrelationUsefulnessVote;
class CorrelationUsefulnessVoteEffectVariableButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID;
	public $qualifiedForeignKeyName = CorrelationUsefulnessVote::TABLE.'.'.CorrelationUsefulnessVote::FIELD_EFFECT_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE.'.'.Variable::FIELD_ID;
	public $childClass = CorrelationUsefulnessVote::class;
	public $parentClass = CorrelationUsefulnessVote::class;
	public $qualifiedParentKeyName = CorrelationUsefulnessVote::TABLE.'.'.CorrelationUsefulnessVote::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'effect_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'effect-variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Effect Variable';
	public $title = 'Effect Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;

}
