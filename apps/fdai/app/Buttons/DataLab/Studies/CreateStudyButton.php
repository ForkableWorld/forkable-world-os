<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\DataLab\Studies;
use App\Buttons\QMButton;
use App\Models\Study;
class CreateStudyButton extends QMButton {
	public $accessibilityText = 'Create Study';
	public $action = 'datalab/studies/create';
	public $color = Study::COLOR;
	public $fontAwesome = Study::FONT_AWESOME;
	public $id = 'datalab-studies-create-button';
	public $image = Study::DEFAULT_IMAGE;
	public $link = 'datalab/studies/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.studies.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\StudyController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\StudyController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Study';
	public $title = 'Create Study';
	public $tooltip = Study::CLASS_DESCRIPTION;
	public $visible = true;
}
