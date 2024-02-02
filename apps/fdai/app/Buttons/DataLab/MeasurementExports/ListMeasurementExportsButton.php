<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\MeasurementExports;
use App\Buttons\QMButton;
use App\Models\MeasurementExport;
class ListMeasurementExportsButton extends QMButton {
	public $accessibilityText = 'List Measurement Exports';
	public $action = 'datalab/measurementExports';
	public $color = MeasurementExport::COLOR;
	public $fontAwesome = MeasurementExport::FONT_AWESOME;
	public $id = 'datalab-measurement-exports-button';
	public $image = MeasurementExport::DEFAULT_IMAGE;
	public $link = 'datalab/measurementExports';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.measurementExports.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\MeasurementExportController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\MeasurementExportController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Measurement Exports';
	public $title = 'List Measurement Exports';
	public $tooltip = MeasurementExport::CLASS_DESCRIPTION;
	public $visible = true;
}
