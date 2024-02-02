<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Variables;
use App\Buttons\QMButton;
use App\Models\Variable;
class CreateVariableButton extends QMButton {
	public $accessibilityText = 'Create Variable';
	public $action = 'datalab/variables/create';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'datalab-variables-create-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $link = 'datalab/variables/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.variables.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\VariableController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\VariableController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Variable';
	public $title = 'Create Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
	public $visible = true;
}
