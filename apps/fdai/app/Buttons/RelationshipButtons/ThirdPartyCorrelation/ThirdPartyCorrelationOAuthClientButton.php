<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ThirdPartyCorrelation;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\OAClient;
use App\Models\ThirdPartyCorrelation;
class ThirdPartyCorrelationOAuthClientButton extends BelongsToRelationshipButton {
    public $interesting = false;
	public $foreignKeyName = OAClient::FIELD_ID;
	public $qualifiedForeignKeyName = ThirdPartyCorrelation::TABLE.'.'.ThirdPartyCorrelation::FIELD_CLIENT_ID;
	public $ownerKeyName = OAClient::FIELD_ID;
	public $qualifiedOwnerKeyName = OAClient::TABLE.'.'.OAClient::FIELD_ID;
	public $childClass = ThirdPartyCorrelation::class;
	public $parentClass = ThirdPartyCorrelation::class;
	public $qualifiedParentKeyName = ThirdPartyCorrelation::TABLE.'.'.ThirdPartyCorrelation::FIELD_ID;
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
