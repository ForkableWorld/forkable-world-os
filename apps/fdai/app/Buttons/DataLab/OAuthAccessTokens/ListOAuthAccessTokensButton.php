<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\OAuthAccessTokens;
use App\Buttons\QMButton;
use App\Models\OAAccessToken;
class ListOAuthAccessTokensButton extends QMButton {
	public $accessibilityText = 'List O Auth Access Tokens';
	public $action = 'datalab/oAuthAccessTokens';
	public $color = OAAccessToken::COLOR;
	public $fontAwesome = OAAccessToken::FONT_AWESOME;
	public $id = 'datalab-auth-access-tokens-button';
	public $image = OAAccessToken::DEFAULT_IMAGE;
	public $link = 'datalab/oAuthAccessTokens';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.oAuthAccessTokens.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\OAAccessTokenController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\OAAccessTokenController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List O Auth Access Tokens';
	public $title = 'List O Auth Access Tokens';
	public $tooltip = OAAccessToken::CLASS_DESCRIPTION;
	public $visible = true;
}
