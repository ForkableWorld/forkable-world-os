<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseCreatedAtProperty;
class TrackingReminderCreatedAtProperty extends BaseCreatedAtProperty
{
    use TrackingReminderProperty;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
}
