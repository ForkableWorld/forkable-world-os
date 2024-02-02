<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\ConnectorImports;
use App\Buttons\QMButton;
use App\Models\ConnectorImport;
class CreateConnectorImportButton extends QMButton {
	public $accessibilityText = 'Create Connector Import';
	public $action = 'datalab/connectorImports/create';
	public $color = ConnectorImport::COLOR;
	public $fontAwesome = ConnectorImport::FONT_AWESOME;
	public $id = 'datalab-connector-imports-create-button';
	public $image = ConnectorImport::DEFAULT_IMAGE;
	public $link = 'datalab/connectorImports/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connectorImports.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectorImportController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectorImportController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Connector Import';
	public $title = 'Create Connector Import';
	public $tooltip = ConnectorImport::CLASS_DESCRIPTION;
	public $visible = true;
}
