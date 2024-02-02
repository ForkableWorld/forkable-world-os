<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class CreateExceptionButton extends AdminButton {
	public $accessibilityText = 'Create Exception';
	public $action = 'admin/create/exception';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-create-exception-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/create/exception';
	public $parameters = [
		'middleware' => [
			'web',
			'admin',
		],
		'uses' => 'App\\Http\\Controllers\\CreateFileController@createException',
		'controller' => 'App\\Http\\Controllers\\CreateFileController@createException',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/admin',
		'where' => [],
		'as' => 'admin.create.exception',
	];
	public $target = 'self';
	public $text = 'Create Exception';
	public $title = 'Create Exception';
	public $tooltip = 'Automated generation of exception classes';
	public $visible = true;
}
