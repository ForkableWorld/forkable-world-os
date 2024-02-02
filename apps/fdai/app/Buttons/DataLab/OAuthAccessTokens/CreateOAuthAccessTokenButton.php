<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\OAuthAccessTokens;
use App\Buttons\QMButton;
use App\Models\OAAccessToken;
class CreateOAuthAccessTokenButton extends QMButton {
	public $accessibilityText = 'Create O Auth Access Token';
	public $action = 'datalab/oAuthAccessTokens/create';
	public $color = OAAccessToken::COLOR;
	public $fontAwesome = OAAccessToken::FONT_AWESOME;
	public $id = 'datalab-auth-access-tokens-create-button';
	public $image = OAAccessToken::DEFAULT_IMAGE;
	public $link = 'datalab/oAuthAccessTokens/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.oAuthAccessTokens.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\OAAccessTokenController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\OAAccessTokenController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create O Auth Access Token';
	public $title = 'Create O Auth Access Token';
	public $tooltip = OAAccessToken::CLASS_DESCRIPTION;
	public $visible = true;
}
