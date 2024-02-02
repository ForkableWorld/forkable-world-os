<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class CreateMigrationButton extends AdminButton {
	public $accessibilityText = 'Create Migration';
	public $action = 'admin/create/migration';
	public $color = '#3467d6';
	public $fontAwesome = 'fas fa-external-link-alt';
	public $id = 'admin-create-migration-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $link = '/admin/create/migration';
	public $parameters = [
		'middleware' => [
			'web',
			'admin',
		],
		'uses' => 'App\\Http\\Controllers\\CreateFileController@createMigration',
		'controller' => 'App\\Http\\Controllers\\CreateFileController@createMigration',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/admin',
		'where' => [],
		'as' => 'admin.create.migration',
	];
	public $target = 'self';
	public $text = 'Create Migration';
	public $title = 'Create Migration';
	public $tooltip = 'Automated generation of database migrations';
	public $visible = true;
}
