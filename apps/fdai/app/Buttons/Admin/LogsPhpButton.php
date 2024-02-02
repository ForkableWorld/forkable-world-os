<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
class LogsPhpButton extends AdminButton {
	public $accessibilityText = 'Logs Php';
	public $action = 'admin/logs/php';
	public $color = '#3467d6';
	public $fontAwesome = 'fab fa-php';
	public $id = 'admin-logs-php-button';
	public $image = 'https://dashboard.snapcraft.io/site_media/appmedia/2017/11/webide.ico_HA9tBL0.png';
	public $link = '/admin/logs/php';
	public $parameters = [
		'middleware' => [
			'web',
			'admin',
		],
		'uses' => 'App\\Http\\Controllers\\Admin\\LogController@getPHPLog',
		'controller' => 'App\\Http\\Controllers\\Admin\\LogController@getPHPLog',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/admin',
		'where' => [],
		'as' => 'admin.logs.php',
	];
	public $target = 'self';
	public $text = 'Logs Php';
	public $title = 'Logs Php';
	public $tooltip = 'Admin Logs Php ' . "\n" . '            title from url: Admin Logs Php' . "\n" .
	'            title from name: Admin logs php' . "\n" . '            action: ';
	public $visible = true;
}
