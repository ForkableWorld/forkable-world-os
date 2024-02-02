<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\MeasurementExports;
use App\Buttons\QMButton;
use App\Models\MeasurementExport;
class CreateMeasurementExportButton extends QMButton {
	public $accessibilityText = 'Create Measurement Export';
	public $action = 'datalab/measurementExports/create';
	public $color = MeasurementExport::COLOR;
	public $fontAwesome = MeasurementExport::FONT_AWESOME;
	public $id = 'datalab-measurement-exports-create-button';
	public $image = MeasurementExport::DEFAULT_IMAGE;
	public $link = 'datalab/measurementExports/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.measurementExports.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\MeasurementExportController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\MeasurementExportController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Measurement Export';
	public $title = 'Create Measurement Export';
	public $tooltip = MeasurementExport::CLASS_DESCRIPTION;
	public $visible = true;
}
