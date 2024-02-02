<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Measurements;
use App\Buttons\QMButton;
use App\Models\Measurement;
class CreateMeasurementButton extends QMButton {
	public $accessibilityText = 'Create Measurement';
	public $action = 'datalab/measurements/create';
	public $color = Measurement::COLOR;
	public $fontAwesome = Measurement::FONT_AWESOME;
	public $id = 'datalab-measurements-create-button';
	public $image = Measurement::DEFAULT_IMAGE;
	public $link = 'datalab/measurements/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.measurements.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\MeasurementController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\MeasurementController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Measurement';
	public $title = 'Create Measurement';
	public $tooltip = 'Measurement';
	public $visible = true;
}
