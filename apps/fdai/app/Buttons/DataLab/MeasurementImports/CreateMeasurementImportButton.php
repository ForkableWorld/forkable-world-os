<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\MeasurementImports;
use App\Buttons\QMButton;
use App\Models\MeasurementImport;
class CreateMeasurementImportButton extends QMButton {
	public $accessibilityText = 'Create Measurement Import';
	public $action = 'datalab/measurementImports/create';
	public $color = MeasurementImport::COLOR;
	public $fontAwesome = MeasurementImport::FONT_AWESOME;
	public $id = 'datalab-measurement-imports-create-button';
	public $image = MeasurementImport::DEFAULT_IMAGE;
	public $link = 'datalab/measurementImports/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.measurementImports.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\MeasurementImportController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\MeasurementImportController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Measurement Import';
	public $title = 'Create Measurement Import';
	public $tooltip = MeasurementImport::CLASS_DESCRIPTION;
	public $visible = true;
}
