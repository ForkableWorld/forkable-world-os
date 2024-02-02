<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\OARefreshToken;
class OAClientOAuthRefreshTokensButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = OARefreshToken::class;
	public $methodName = OARefreshToken::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = OARefreshToken::COLOR;
	public $fontAwesome = OARefreshToken::FONT_AWESOME;
	public $id = 'oauth-refresh-tokens-button';
	public $image = OARefreshToken::DEFAULT_IMAGE;
	public $text = 'OAuth Refresh Tokens';
	public $title = 'OAuth Refresh Tokens';
	public $tooltip = OARefreshToken::CLASS_DESCRIPTION;
}
