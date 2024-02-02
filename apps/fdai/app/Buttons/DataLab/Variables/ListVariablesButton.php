<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Variables;
use App\Buttons\QMButton;
use App\Models\Variable;
class ListVariablesButton extends QMButton {
	public $accessibilityText = 'List Variables';
	public $action = 'datalab/variables';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'datalab-variables-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $link = 'datalab/variables';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.variables.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\VariableController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\VariableController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Variables';
	public $title = 'List Variables';
	public $tooltip = Variable::CLASS_DESCRIPTION;
	public $visible = true;
}
