<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLab\TrackingReminders;
use App\Buttons\QMButton;
use App\Models\TrackingReminder;
class CreateTrackingReminderButton extends QMButton {
	public $accessibilityText = 'Create Tracking Reminder';
	public $action = 'datalab/trackingReminders/create';
	public $color = TrackingReminder::COLOR;
	public $fontAwesome = TrackingReminder::FONT_AWESOME;
	public $id = 'datalab-tracking-reminders-create-button';
	public $image = TrackingReminder::DEFAULT_IMAGE;
	public $link = 'datalab/trackingReminders/create';
	public $parameters = [
		'middleware' => [
			'web',
			'auth',
		],
		'as' => 'datalab.trackingReminders.create',
		'uses' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderController@create',
		'controller' => 'App\\Http\\Controllers\\DataLab\\TrackingReminderController@create',
		'namespace' => 'App\\Http\\Controllers',
		'prefix' => '/datalab',
		'where' => [],
	];
	public $target = 'self';
	public $text = 'Create Tracking Reminder';
	public $title = 'Create Tracking Reminder';
	public $tooltip = 'Favorite';
	public $visible = true;
}
