<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Applications;
use App\Buttons\QMButton;
use App\Models\Application;
class ListApplicationsButton extends QMButton {
	public $accessibilityText = 'List Applications';
	public $action = 'datalab/applications';
	public $color = Application::COLOR;
	public $fontAwesome = Application::FONT_AWESOME;
	public $id = 'datalab-applications-button';
	public $image = Application::DEFAULT_IMAGE;
	public $link = 'datalab/applications';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.applications.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\ApplicationController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\ApplicationController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Applications';
	public $title = 'List Applications';
	public $tooltip = Application::CLASS_DESCRIPTION;
	public $visible = true;
}
