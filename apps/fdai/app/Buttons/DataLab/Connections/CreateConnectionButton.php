<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Connections;
use App\Buttons\QMButton;
use App\Models\Connection;
class CreateConnectionButton extends QMButton {
	public $accessibilityText = 'Create Connection';
	public $action = 'datalab/connections/create';
	public $color = Connection::COLOR;
	public $fontAwesome = Connection::FONT_AWESOME;
	public $id = 'datalab-connections-create-button';
	public $image = Connection::DEFAULT_IMAGE;
	public $link = 'datalab/connections/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connections.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectionController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectionController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Connection';
	public $title = 'Create Connection';
	public $tooltip = ' new measurements imported never';
	public $visible = true;
}
