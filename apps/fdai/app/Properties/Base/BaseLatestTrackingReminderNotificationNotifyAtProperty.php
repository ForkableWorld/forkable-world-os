<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Logging\QMLog;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Properties\BaseProperty;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\IsDateTime;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class BaseLatestTrackingReminderNotificationNotifyAtProperty extends BaseProperty{
	use IsDateTime;
    const MAX_FUTURE_SECONDS = 60 * 86400;
    public $dbInput = 'datetime:nullable';
	public $dbType = \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'latest_tracking_reminder_notification_notify_at';
	public $example = '2016-11-04 03:00:00';
	public $fieldType = self::TYPE_DATETIME;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $format = 'date-time';
	public $htmlInput = 'date';
	public $htmlType = 'date';
	public $image = ImageUrls::COMBINE_NOTIFICATIONS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'latest_tracking_reminder_notification_notify_at';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|date';
	public $title = 'Latest Tracking Reminder Notification Notify';
	public $type = self::TYPE_DATETIME;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|date';
    public static function updateAll(){
        QMLog::infoWithoutContext(
			"Updating latest notification time in the reminders table from the notifications table...");
	    Writable::db()->table('tracking_reminders')
	      ->update([
		               'tracking_reminders.latest_tracking_reminder_notification_notify_at' =>  Writable::db()->raw(
			               '(SELECT MAX(tracking_reminder_notifications.notify_at) FROM tracking_reminder_notifications WHERE tracking_reminders.id = tracking_reminder_notifications.tracking_reminder_id)'
		               )
	               ]);
//	    Writable::pdoStatement('
//            UPDATE tracking_reminders
//                SET tracking_reminders.latest_tracking_reminder_notification_notify_at =
//                  ( SELECT MAX(tracking_reminder_notifications.notify_at)
//                    FROM tracking_reminder_notifications
//                    WHERE tracking_reminders.id = tracking_reminder_notifications.tracking_reminder_id
//                  )
//        ');
		$reminderIds = TrackingReminderNotification::groupBy('tracking_reminder_id')
		                                           ->pluck('tracking_reminder_id');
		TrackingReminder::whereNotIn('id', $reminderIds)
		                ->update([TrackingReminder::FIELD_LATEST_TRACKING_REMINDER_NOTIFICATION_NOTIFY_AT => null]);
//        QMLog::infoWithoutContext("Updating latest notification time in the reminders table from reminder " .
//            "start_tracking_date where we do not have notifications yet...");
//        Writable::runStatement('
//            UPDATE tracking_reminders
//            SET latest_tracking_reminder_notification_notify_at =
//                FROM_UNIXTIME((UNIX_TIMESTAMP(CONCAT(start_tracking_date, " ", reminder_start_time))) - reminder_frequency)
//            WHERE reminder_frequency > 0 AND latest_tracking_reminder_notification_notify_at IS NULL
//        ');
    }
	public function getLatestUnixTime(): int { return time() + self::MAX_FUTURE_SECONDS; }
    public function getLatestAt(): string { return db_date($this->getLatestUnixTime()); }
    public function showOnIndex(): bool {return true;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
