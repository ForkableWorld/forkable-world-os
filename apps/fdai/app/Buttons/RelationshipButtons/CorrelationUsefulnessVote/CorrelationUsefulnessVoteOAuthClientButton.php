<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\CorrelationUsefulnessVote;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\OAClient;
use App\Models\CorrelationUsefulnessVote;
class CorrelationUsefulnessVoteOAuthClientButton extends BelongsToRelationshipButton {
    public $interesting = false;
	public $foreignKeyName = OAClient::FIELD_ID;
	public $qualifiedForeignKeyName = CorrelationUsefulnessVote::TABLE.'.'.CorrelationUsefulnessVote::FIELD_CLIENT_ID;
	public $ownerKeyName = OAClient::FIELD_ID;
	public $qualifiedOwnerKeyName = OAClient::TABLE.'.'.OAClient::FIELD_ID;
	public $childClass = CorrelationUsefulnessVote::class;
	public $parentClass = CorrelationUsefulnessVote::class;
	public $qualifiedParentKeyName = CorrelationUsefulnessVote::TABLE.'.'.CorrelationUsefulnessVote::FIELD_ID;
	public $relatedClass = OAClient::class;
	public $methodName = 'oa_client';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = OAClient::COLOR;
	public $fontAwesome = OAClient::FONT_AWESOME;
	public $id = 'oauth-client-button';
	public $image = OAClient::DEFAULT_IMAGE;
	public $text = 'OAuth Client';
	public $title = 'OAuth Client';
	public $tooltip = OAClient::CLASS_DESCRIPTION;

}
