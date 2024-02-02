<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Applications;
use App\Buttons\QMButton;
use App\Models\Application;
class CreateApplicationButton extends QMButton {
	public $accessibilityText = 'Create Application';
	public $action = 'datalab/applications/create';
	public $color = Application::COLOR;
	public $fontAwesome = Application::FONT_AWESOME;
	public $id = 'datalab-applications-create-button';
	public $image = Application::DEFAULT_IMAGE;
	public $link = 'datalab/applications/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.applications.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ApplicationController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ApplicationController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Application';
	public $title = 'Create Application';
	public $tooltip = Application::CLASS_DESCRIPTION;
	public $visible = true;
}
