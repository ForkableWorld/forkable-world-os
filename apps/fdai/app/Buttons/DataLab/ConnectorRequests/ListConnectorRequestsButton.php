<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\ConnectorRequests;
use App\Buttons\QMButton;
use App\Models\ConnectorRequest;
class ListConnectorRequestsButton extends QMButton {
	public $accessibilityText = 'List Connector Requests';
	public $action = 'datalab/connectorRequests';
	public $color = ConnectorRequest::COLOR;
	public $fontAwesome = ConnectorRequest::FONT_AWESOME;
	public $id = 'datalab-connector-requests-button';
	public $image = ConnectorRequest::DEFAULT_IMAGE;
	public $link = 'datalab/connectorRequests';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connectorRequests.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectorRequestController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectorRequestController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Connector Requests';
	public $title = 'List Connector Requests';
	public $tooltip = ConnectorRequest::CLASS_DESCRIPTION;
	public $visible = true;
}
