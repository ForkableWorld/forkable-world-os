<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\TrackingReminderNotifications;
use App\Buttons\QMButton;
use App\Models\TrackingReminderNotification;
class CreateTrackingReminderNotificationButton extends QMButton {
	public $accessibilityText = 'Create Tracking Reminder Notification';
	public $action = 'datalab/trackingReminderNotifications/create';
	public $color = TrackingReminderNotification::COLOR;
	public $fontAwesome = TrackingReminderNotification::FONT_AWESOME;
	public $id = 'datalab-tracking-reminder-notifications-create-button';
	public $image = TrackingReminderNotification::DEFAULT_IMAGE;
	public $link = 'datalab/trackingReminderNotifications/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.trackingReminderNotifications.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderNotificationController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderNotificationController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Tracking Reminder Notification';
	public $title = 'Create Tracking Reminder Notification';
	public $tooltip = TrackingReminderNotification::CLASS_DESCRIPTION;
	public $visible = true;
}
