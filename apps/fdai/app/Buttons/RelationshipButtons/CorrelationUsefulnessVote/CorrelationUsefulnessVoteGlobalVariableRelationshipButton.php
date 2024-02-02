<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\CorrelationUsefulnessVote;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\GlobalVariableRelationship;
use App\Models\CorrelationUsefulnessVote;
class CorrelationUsefulnessVoteGlobalVariableRelationshipButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID;
	public $qualifiedForeignKeyName = CorrelationUsefulnessVote::TABLE.'.'.CorrelationUsefulnessVote::FIELD_AGGREGATE_CORRELATION_ID;
	public $ownerKeyName = GlobalVariableRelationship::FIELD_ID;
	public $qualifiedOwnerKeyName = GlobalVariableRelationship::TABLE.'.'.GlobalVariableRelationship::FIELD_ID;
	public $childClass = CorrelationUsefulnessVote::class;
	public $parentClass = CorrelationUsefulnessVote::class;
	public $qualifiedParentKeyName = CorrelationUsefulnessVote::TABLE.'.'.CorrelationUsefulnessVote::FIELD_ID;
	public $relatedClass = GlobalVariableRelationship::class;
	public $methodName = 'global_variable_relationship';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = GlobalVariableRelationship::COLOR;
	public $fontAwesome = GlobalVariableRelationship::FONT_AWESOME;
	public $id = 'global-variable-relationship-button';
	public $image = GlobalVariableRelationship::DEFAULT_IMAGE;
	public $text = 'Global Variable Relationship';
	public $title = 'Global Variable Relationship';
	public $tooltip = GlobalVariableRelationship::CLASS_DESCRIPTION;

}
