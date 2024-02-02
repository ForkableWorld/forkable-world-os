<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Connectors;
use App\Buttons\QMButton;
use App\Models\Connector;
class ListConnectorsButton extends QMButton {
	public $accessibilityText = 'List Connectors';
	public $action = 'datalab/connectors';
	public $color = Connector::COLOR;
	public $fontAwesome = Connector::FONT_AWESOME;
	public $id = 'datalab-connectors-button';
	public $image = Connector::DEFAULT_IMAGE;
	public $link = 'datalab/connectors';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connectors.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectorController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectorController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Connectors';
	public $title = 'List Connectors';
	public $tooltip = Connector::CLASS_DESCRIPTION;
	public $visible = true;
}
