<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Users;
use App\Buttons\QMButton;
use App\Models\User;
class ListUsersButton extends QMButton {
	public $accessibilityText = 'List Users';
	public $action = 'datalab/users';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'datalab-users-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $link = 'datalab/users';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.users.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\UserController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\UserController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Users';
	public $title = 'List Users';
	public $tooltip = 'Overview of discoveries, research from \'s data';
	public $visible = true;
}
