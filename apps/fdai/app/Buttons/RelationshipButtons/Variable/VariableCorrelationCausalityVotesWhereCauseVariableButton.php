<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\CorrelationCausalityVote;
use App\Models\Variable;
class VariableCorrelationCausalityVotesWhereCauseVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = CorrelationCausalityVote::class;
	public $methodName = 'correlation_causality_votes_where_cause_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = CorrelationCausalityVote::COLOR;
	public $fontAwesome = CorrelationCausalityVote::FONT_AWESOME;
	public $id = 'correlation-causality-votes-where-cause-variable-button';
	public $image = CorrelationCausalityVote::DEFAULT_IMAGE;
	public $text = 'User Variable Relationship Causality Votes Where Cause Variable';
	public $title = 'User Variable Relationship Causality Votes Where Cause Variable';
	public $tooltip = 'User Variable Relationship Causality Votes where this is the Cause Variable';
}
