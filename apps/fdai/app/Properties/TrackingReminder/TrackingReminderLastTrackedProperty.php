<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Models\TrackingReminder;
use App\Storage\DB\Writable;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseLastTrackedProperty;
class TrackingReminderLastTrackedProperty extends BaseLastTrackedProperty
{
    use TrackingReminderProperty;
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
    public static function updateAll(){
	    Writable::db()->table('tracking_reminders')
	      ->join('user_variables', 'tracking_reminders.user_variable_id', '=', 'user_variables.id')
		    ->where('tracking_reminders.last_tracked', "<", 'user_variables.latest_non_tagged_measurement_start_at')
		    ->orWhere('tracking_reminders.last_tracked', null)
	      ->update([
		               'tracking_reminders.last_tracked' => 'user_variables.latest_non_tagged_measurement_start_at'
	               ]);
//        db_statement("update tracking_reminders 
//    join user_variables on tracking_reminders.user_variable_id = user_variables.id 
//            set tracking_reminders.last_tracked = user_variables.latest_non_tagged_measurement_start_at");
    }
}
