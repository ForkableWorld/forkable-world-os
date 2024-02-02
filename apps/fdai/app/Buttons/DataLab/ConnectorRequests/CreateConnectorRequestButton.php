<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\ConnectorRequests;
use App\Buttons\QMButton;
use App\Models\ConnectorRequest;
class CreateConnectorRequestButton extends QMButton {
	public $accessibilityText = 'Create Connector Request';
	public $action = 'datalab/connectorRequests/create';
	public $color = ConnectorRequest::COLOR;
	public $fontAwesome = ConnectorRequest::FONT_AWESOME;
	public $id = 'datalab-connector-requests-create-button';
	public $image = ConnectorRequest::DEFAULT_IMAGE;
	public $link = 'datalab/connectorRequests/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connectorRequests.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectorRequestController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectorRequestController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Connector Request';
	public $title = 'Create Connector Request';
	public $tooltip = ConnectorRequest::CLASS_DESCRIPTION;
	public $visible = true;
}
