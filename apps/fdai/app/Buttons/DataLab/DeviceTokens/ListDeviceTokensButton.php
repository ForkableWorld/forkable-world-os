<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\DeviceTokens;
use App\Buttons\QMButton;
use App\Models\DeviceToken;
class ListDeviceTokensButton extends QMButton {
	public $accessibilityText = 'List Device Tokens';
	public $action = 'datalab/deviceTokens';
	public $color = DeviceToken::COLOR;
	public $fontAwesome = DeviceToken::FONT_AWESOME;
	public $id = 'datalab-device-tokens-button';
	public $image = DeviceToken::DEFAULT_IMAGE;
	public $link = 'datalab/deviceTokens';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.deviceTokens.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\DeviceTokenController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\DeviceTokenController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Device Tokens';
	public $title = 'List Device Tokens';
	public $tooltip = DeviceToken::CLASS_DESCRIPTION;
	public $visible = true;
}
