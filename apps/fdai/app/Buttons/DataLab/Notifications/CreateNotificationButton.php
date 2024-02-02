<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\Notifications;
use App\Buttons\QMButton;
use App\Models\Notification;
class CreateNotificationButton extends QMButton {
	public $accessibilityText = 'Create Notification';
	public $action = 'datalab/notifications/create';
	public $color = Notification::COLOR;
	public $fontAwesome = Notification::FONT_AWESOME;
	public $id = 'datalab-notifications-create-button';
	public $image = Notification::DEFAULT_IMAGE;
	public $link = 'datalab/notifications/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.notifications.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\NotificationController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\NotificationController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Notification';
	public $title = 'Create Notification';
	public $tooltip = Notification::CLASS_DESCRIPTION;
	public $visible = true;
}
