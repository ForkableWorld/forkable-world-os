<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\UserVariables;
use App\Buttons\QMButton;
use App\Models\UserVariable;
class CreateUserVariableButton extends QMButton {
	public $accessibilityText = 'Create User Variable';
	public $action = 'datalab/userVariables/create';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'datalab-user-variables-create-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $link = 'datalab/userVariables/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.userVariables.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\UserVariableController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\UserVariableController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create User Variable';
	public $title = 'Create User Variable';
	public $tooltip = UserVariable::CLASS_DESCRIPTION;
	public $visible = true;
}
