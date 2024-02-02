<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class UserManagementCreateButton extends AdminButton {
	public $accessibilityText = 'User-Management Create';
	public $action = 'admin/user-management/create';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-user-management-create-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/user-management/create';
	public $parameters = [
		'middleware' => [
			'web',
			'admin',
		],
		'as' => 'user-management.create',
		'uses' => 'App\\Http\\Controllers\\UserManagementController@create',
		'controller' => 'App\\Http\\Controllers\\UserManagementController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/admin',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'User-Management Create';
	public $title = 'User-Management Create';
	public $tooltip = 'User-Management Create ' . "\n" . '            title from url: Admin User-Management Create' .
	"\n" . '            title from name: User-management create' . "\n" . '            action: ';
	public $visible = true;
}
