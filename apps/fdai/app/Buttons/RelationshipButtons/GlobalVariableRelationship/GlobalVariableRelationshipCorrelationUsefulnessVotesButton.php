<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\GlobalVariableRelationship;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\CorrelationUsefulnessVote;
use App\Models\GlobalVariableRelationship;
class GlobalVariableRelationshipCorrelationUsefulnessVotesButton extends HasManyRelationshipButton {
    public $interesting = true;
	public $parentClass = GlobalVariableRelationship::class;
	public $qualifiedParentKeyName = GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_ID;
	public $relatedClass = CorrelationUsefulnessVote::class;
	public $methodName = CorrelationUsefulnessVote::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = CorrelationUsefulnessVote::COLOR;
	public $fontAwesome = CorrelationUsefulnessVote::FONT_AWESOME;
	public $id = 'correlation-usefulness-votes-button';
	public $image = CorrelationUsefulnessVote::DEFAULT_IMAGE;
	public $text = 'User Variable Relationship Usefulness Votes';
	public $title = 'User Variable Relationship Usefulness Votes';
	public $tooltip = CorrelationUsefulnessVote::CLASS_DESCRIPTION;

}
