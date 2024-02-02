<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\DeviceTokens;
use App\Buttons\QMButton;
use App\Models\DeviceToken;
class CreateDeviceTokenButton extends QMButton {
	public $accessibilityText = 'Create Device Token';
	public $action = 'datalab/deviceTokens/create';
	public $color = DeviceToken::COLOR;
	public $fontAwesome = DeviceToken::FONT_AWESOME;
	public $id = 'datalab-device-tokens-create-button';
	public $image = DeviceToken::DEFAULT_IMAGE;
	public $link = 'datalab/deviceTokens/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.deviceTokens.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\DeviceTokenController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\DeviceTokenController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Device Token';
	public $title = 'Create Device Token';
	public $tooltip = DeviceToken::CLASS_DESCRIPTION;
	public $visible = true;
}
