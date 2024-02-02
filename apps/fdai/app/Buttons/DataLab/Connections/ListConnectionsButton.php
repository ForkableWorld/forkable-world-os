<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Connections;
use App\Buttons\QMButton;
use App\Models\Connection;
class ListConnectionsButton extends QMButton {
	public $accessibilityText = 'List Connections';
	public $action = 'datalab/connections';
	public $color = Connection::COLOR;
	public $fontAwesome = Connection::FONT_AWESOME;
	public $id = 'datalab-connections-button';
	public $image = Connection::DEFAULT_IMAGE;
	public $link = 'datalab/connections';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connections.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectionController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectionController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Connections';
	public $title = 'List Connections';
	public $tooltip = ' new measurements imported never';
	public $visible = true;
}
