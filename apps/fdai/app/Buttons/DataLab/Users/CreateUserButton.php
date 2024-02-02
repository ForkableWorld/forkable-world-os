<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Users;
use App\Buttons\QMButton;
use App\Models\User;
class CreateUserButton extends QMButton {
	public $accessibilityText = 'Create User';
	public $action = 'datalab/users/create';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'datalab-users-create-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $link = 'datalab/users/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.users.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\UserController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\UserController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create User';
	public $title = 'Create User';
	public $tooltip = 'Overview of discoveries, research from \'s data';
	public $visible = true;
}
