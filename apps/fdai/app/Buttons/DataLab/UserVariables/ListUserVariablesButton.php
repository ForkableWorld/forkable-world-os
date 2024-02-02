<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\UserVariables;
use App\Buttons\QMButton;
use App\Models\UserVariable;
class ListUserVariablesButton extends QMButton {
	public $accessibilityText = 'List User Variables';
	public $action = 'datalab/userVariables';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'datalab-user-variables-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $link = 'datalab/userVariables';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.userVariables.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\UserVariableController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\UserVariableController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List User Variables';
	public $title = 'List User Variables';
	public $tooltip = UserVariable::CLASS_DESCRIPTION;
	public $visible = true;
}
