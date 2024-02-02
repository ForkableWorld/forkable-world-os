<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\OAuthClients;
use App\Buttons\QMButton;
use App\Models\OAClient;
class ListOAuthClientsButton extends QMButton {
	public $accessibilityText = 'List O Auth Clients';
	public $action = 'datalab/oAuthClients';
	public $color = OAClient::COLOR;
	public $fontAwesome = OAClient::FONT_AWESOME;
	public $id = 'datalab-auth-clients-button';
	public $image = OAClient::DEFAULT_IMAGE;
	public $link = 'datalab/oAuthClients';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.oAuthClients.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\OAClientController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\OAClientController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List O Auth Clients';
	public $title = 'List O Auth Clients';
	public $tooltip = OAClient::CLASS_DESCRIPTION;
	public $visible = true;
}
