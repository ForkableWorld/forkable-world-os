<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Notifications;
use App\Buttons\QMButton;
use App\Models\Notification;
class ListNotificationsButton extends QMButton {
	public $accessibilityText = 'List Notifications';
	public $action = 'datalab/notifications';
	public $color = Notification::COLOR;
	public $fontAwesome = Notification::FONT_AWESOME;
	public $id = 'datalab-notifications-button';
	public $image = Notification::DEFAULT_IMAGE;
	public $link = 'datalab/notifications';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.notifications.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\NotificationController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\NotificationController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Notifications';
	public $title = 'List Notifications';
	public $tooltip = Notification::CLASS_DESCRIPTION;
	public $visible = true;
}
