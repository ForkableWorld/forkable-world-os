<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\CorrelationCausalityVote;
use App\Models\User;
class UserVariableRelationshipCausalityVotesButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = CorrelationCausalityVote::class;
	public $methodName = CorrelationCausalityVote::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = CorrelationCausalityVote::COLOR;
	public $fontAwesome = CorrelationCausalityVote::FONT_AWESOME;
	public $id = 'correlation-causality-votes-button';
	public $image = CorrelationCausalityVote::DEFAULT_IMAGE;
	public $text = 'User Variable Relationship Causality Votes';
	public $title = 'User Variable Relationship Causality Votes';
	public $tooltip = CorrelationCausalityVote::CLASS_DESCRIPTION;
}
