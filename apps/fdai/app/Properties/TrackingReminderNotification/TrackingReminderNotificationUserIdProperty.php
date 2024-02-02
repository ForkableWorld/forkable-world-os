<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminderNotification;
use App\Models\TrackingReminderNotification;
use App\Traits\PropertyTraits\TrackingReminderNotificationProperty;
use App\Properties\Base\BaseUserIdProperty;
use App\Traits\HasUserFilter;
class TrackingReminderNotificationUserIdProperty extends BaseUserIdProperty
{
    use TrackingReminderNotificationProperty;
    use HasUserFilter;
    public $table = TrackingReminderNotification::TABLE;
    public $parentClass = TrackingReminderNotification::class;
}
