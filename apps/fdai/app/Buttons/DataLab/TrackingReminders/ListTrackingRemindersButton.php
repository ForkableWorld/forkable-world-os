<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\TrackingReminders;
use App\Buttons\QMButton;
use App\Models\TrackingReminder;
class ListTrackingRemindersButton extends QMButton {
	public $accessibilityText = 'List Tracking Reminders';
	public $action = 'datalab/trackingReminders';
	public $color = TrackingReminder::COLOR;
	public $fontAwesome = TrackingReminder::FONT_AWESOME;
	public $id = 'datalab-tracking-reminders-button';
	public $image = TrackingReminder::DEFAULT_IMAGE;
	public $link = 'datalab/trackingReminders';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.trackingReminders.index',
		'uses' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderController@index',
		'controller' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderController@index',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'List Tracking Reminders';
	public $title = 'List Tracking Reminders';
	public $tooltip = 'Favorite';
	public $visible = true;
}
