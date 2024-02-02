<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Studies;
use App\Buttons\QMButton;
use App\Models\Study;
class ListStudiesButton extends QMButton {
	public $accessibilityText = 'List Studies';
	public $action = 'datalab/studies';
	public $color = Study::COLOR;
	public $fontAwesome = Study::FONT_AWESOME;
	public $id = 'datalab-studies-button';
	public $image = Study::DEFAULT_IMAGE;
	public $link = 'datalab/studies';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.studies.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\StudyController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\StudyController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Studies';
	public $title = 'List Studies';
	public $tooltip = Study::CLASS_DESCRIPTION;
	public $visible = true;
}
