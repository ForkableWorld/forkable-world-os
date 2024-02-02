<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminderNotification;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\TrackingReminderNotification;
use App\Traits\PropertyTraits\TrackingReminderNotificationProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class TrackingReminderNotificationIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use TrackingReminderNotificationProperty;
    public $table = TrackingReminderNotification::TABLE;
    public $parentClass = TrackingReminderNotification::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'tracking_reminder_notification_id',
        'id',
    ];
}
