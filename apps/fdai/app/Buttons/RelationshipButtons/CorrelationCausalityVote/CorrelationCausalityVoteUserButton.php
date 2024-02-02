<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\CorrelationCausalityVote;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\User;
use App\Models\CorrelationCausalityVote;
class CorrelationCausalityVoteUserButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = CorrelationCausalityVote::FIELD_USER_ID;
	public $qualifiedForeignKeyName = CorrelationCausalityVote::TABLE.'.'.CorrelationCausalityVote::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE.'.'.User::FIELD_ID;
	public $childClass = CorrelationCausalityVote::class;
	public $parentClass = CorrelationCausalityVote::class;
	public $qualifiedParentKeyName = CorrelationCausalityVote::TABLE.'.'.CorrelationCausalityVote::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = CorrelationCausalityVote::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;

}
