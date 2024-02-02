<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\MeasurementImports;
use App\Buttons\QMButton;
use App\Models\MeasurementImport;
class ListMeasurementImportsButton extends QMButton {
	public $accessibilityText = 'List Measurement Imports';
	public $action = 'datalab/measurementImports';
	public $color = MeasurementImport::COLOR;
	public $fontAwesome = MeasurementImport::FONT_AWESOME;
	public $id = 'datalab-measurement-imports-button';
	public $image = MeasurementImport::DEFAULT_IMAGE;
	public $link = 'datalab/measurementImports';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.measurementImports.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\MeasurementImportController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\MeasurementImportController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Measurement Imports';
	public $title = 'List Measurement Imports';
	public $tooltip = MeasurementImport::CLASS_DESCRIPTION;
	public $visible = true;
}
