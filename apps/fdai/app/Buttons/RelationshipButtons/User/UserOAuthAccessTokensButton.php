<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAAccessToken;
use App\Models\User;
class UserOAuthAccessTokensButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'access_token';
	public $relatedClass = OAAccessToken::class;
	public $methodName = OAAccessToken::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = OAAccessToken::COLOR;
	public $fontAwesome = OAAccessToken::FONT_AWESOME;
	public $id = 'oauth-access-tokens-button';
	public $image = OAAccessToken::DEFAULT_IMAGE;
	public $text = 'OAuth Access Tokens';
	public $title = 'OAuth Access Tokens';
	public $tooltip = OAAccessToken::CLASS_DESCRIPTION;
}
