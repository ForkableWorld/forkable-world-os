<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\CorrelationCausalityVote;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserVariableRelationship;
use App\Models\CorrelationCausalityVote;
class CorrelationCausalityVoteCorrelationButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = CorrelationCausalityVote::FIELD_CORRELATION_ID;
	public $qualifiedForeignKeyName = CorrelationCausalityVote::TABLE.'.'.CorrelationCausalityVote::FIELD_CORRELATION_ID;
	public $ownerKeyName = UserVariableRelationship::FIELD_ID;
	public $qualifiedOwnerKeyName = UserVariableRelationship::TABLE.'.'.UserVariableRelationship::FIELD_ID;
	public $childClass = CorrelationCausalityVote::class;
	public $parentClass = CorrelationCausalityVote::class;
	public $qualifiedParentKeyName = CorrelationCausalityVote::TABLE.'.'.CorrelationCausalityVote::FIELD_ID;
	public $relatedClass = UserVariableRelationship::class;
	public $methodName = 'correlation';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = UserVariableRelationship::COLOR;
	public $fontAwesome = UserVariableRelationship::FONT_AWESOME;
	public $id = 'correlation-button';
	public $image = UserVariableRelationship::DEFAULT_IMAGE;
	public $text = 'UserVariableRelationship';
	public $title = 'UserVariableRelationship';
	public $tooltip = UserVariableRelationship::CLASS_DESCRIPTION;

}
