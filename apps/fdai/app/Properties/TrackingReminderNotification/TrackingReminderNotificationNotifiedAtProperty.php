<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminderNotification;
use App\Models\TrackingReminderNotification;
use App\Traits\PropertyTraits\TrackingReminderNotificationProperty;
use App\Properties\Base\BaseNotifiedAtProperty;
class TrackingReminderNotificationNotifiedAtProperty extends BaseNotifiedAtProperty
{
    use TrackingReminderNotificationProperty;
    public $table = TrackingReminderNotification::TABLE;
    public $parentClass = TrackingReminderNotification::class;
    public function showOnIndex(): bool {return false;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return true;}
}
