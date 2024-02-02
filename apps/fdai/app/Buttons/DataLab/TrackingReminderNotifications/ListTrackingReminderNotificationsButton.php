<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\TrackingReminderNotifications;
use App\Buttons\QMButton;
use App\Models\TrackingReminderNotification;
class ListTrackingReminderNotificationsButton extends QMButton {
	public $accessibilityText = 'List Tracking Reminder Notifications';
	public $action = 'datalab/trackingReminderNotifications';
	public $color = TrackingReminderNotification::COLOR;
	public $fontAwesome = TrackingReminderNotification::FONT_AWESOME;
	public $id = 'datalab-tracking-reminder-notifications-button';
	public $image = TrackingReminderNotification::DEFAULT_IMAGE;
	public $link = 'datalab/trackingReminderNotifications';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.trackingReminderNotifications.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderNotificationController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderNotificationController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Tracking Reminder Notifications';
	public $title = 'List Tracking Reminder Notifications';
	public $tooltip = TrackingReminderNotification::CLASS_DESCRIPTION;
	public $visible = true;
}
