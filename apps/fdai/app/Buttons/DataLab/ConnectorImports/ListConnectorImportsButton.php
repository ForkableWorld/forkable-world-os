<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\ConnectorImports;
use App\Buttons\QMButton;
use App\Models\ConnectorImport;
class ListConnectorImportsButton extends QMButton {
	public $accessibilityText = 'List Connector Imports';
	public $action = 'datalab/connectorImports';
	public $color = ConnectorImport::COLOR;
	public $fontAwesome = ConnectorImport::FONT_AWESOME;
	public $id = 'datalab-connector-imports-button';
	public $image = ConnectorImport::DEFAULT_IMAGE;
	public $link = 'datalab/connectorImports';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.connectorImports.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ConnectorImportController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ConnectorImportController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Connector Imports';
	public $title = 'List Connector Imports';
	public $tooltip = ConnectorImport::CLASS_DESCRIPTION;
	public $visible = true;
}
