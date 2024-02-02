<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\OAuthClients;
use App\Buttons\QMButton;
use App\Models\OAClient;
class CreateOAuthClientButton extends QMButton {
	public $accessibilityText = 'Create O Auth Client';
	public $action = 'datalab/oAuthClients/create';
	public $color = OAClient::COLOR;
	public $fontAwesome = OAClient::FONT_AWESOME;
	public $id = 'datalab-auth-clients-create-button';
	public $image = OAClient::DEFAULT_IMAGE;
	public $link = 'datalab/oAuthClients/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.oAuthClients.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\OAClientController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\OAClientController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create O Auth Client';
	public $title = 'Create O Auth Client';
	public $tooltip = OAClient::CLASS_DESCRIPTION;
	public $visible = true;
}
