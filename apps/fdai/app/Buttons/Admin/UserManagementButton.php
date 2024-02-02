<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class UserManagementButton extends AdminButton {
	public $accessibilityText = 'User-Management';
	public $action = 'admin/user-management';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-user-management-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/user-management';
	public $parameters = [
		'middleware' => [
			'web',
			'admin',
		],
		'as' => 'user-management.index',
		'uses' => 'App\\Http\\Controllers\\UserManagementController@index',
		'controller' => 'App\\Http\\Controllers\\UserManagementController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/admin',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'User-Management';
	public $title = 'User-Management';
	public $tooltip = 'User-Management Index ';
	public $visible = true;
}
