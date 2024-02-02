<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Connectors;
use App\Buttons\QMButton;
use App\Models\Connector;
class CreateConnectorButton extends QMButton {
	public $accessibilityText = 'Create Connector';
	public $action = 'datalab/connectors/create';
	public $color = Connector::COLOR;
	public $fontAwesome = Connector::FONT_AWESOME;
	public $id = 'datalab-connectors-create-button';
	public $image = Connector::DEFAULT_IMAGE;
	public $link = 'datalab/connectors/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connectors.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectorController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectorController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Connector';
	public $title = 'Create Connector';
	public $tooltip = Connector::CLASS_DESCRIPTION;
	public $visible = true;
}
