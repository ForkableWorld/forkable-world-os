<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Measurements;
use App\Buttons\QMButton;
use App\Models\Measurement;
class ListMeasurementsButton extends QMButton {
	public $accessibilityText = 'List Measurements';
	public $action = 'datalab/measurements';
	public $color = Measurement::COLOR;
	public $fontAwesome = Measurement::FONT_AWESOME;
	public $id = 'datalab-measurements-button';
	public $image = Measurement::DEFAULT_IMAGE;
	public $link = 'datalab/measurements';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.measurements.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\MeasurementController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\MeasurementController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Measurements';
	public $title = 'List Measurements';
	public $tooltip = 'Measurement';
	public $visible = true;
}
