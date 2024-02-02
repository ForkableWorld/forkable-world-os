<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OARefreshToken;
use App\Models\User;
class UserOAuthRefreshTokensButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = User::TABLE . '.' . User::FIELD_CLIENT_ID;
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
