<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Storage\DB\Writable;
class TextMessageNotification {
	public const ERROR_RESPONSE_NOT_REGISTERED = "NotRegistered";
	public const MINUTES_BETWEEN_CHECKS_FOR_NEW_NOTIFICATIONS = 15;
	public $result;
	public function __construct(){
	}
	/**
	 * @param $debugUser
	 * @return array|static[]
	 */
	public static function getPhoneNumbersThatNeedNotifications($debugUser){
		//Using $writableConnection to make sure there's no discrepancy with the tokens we just updated
		$phoneNumberObjectsQuery = Writable::db()->table('wp_users as wu')->select([
				'wu.user_id',
				'wu.phone_number',
			])
			->whereRaw("wu.latest_reminder_time > TIME_FORMAT(NOW() - INTERVAL wu.time_zone_offset MINUTE, '%H:%i:%s')")
			->whereRaw("wu.earliest_reminder_time < TIME_FORMAT(NOW() - INTERVAL wu.time_zone_offset MINUTE, '%H:%i:%s')")
			->whereRaw("wu.phone_number NOT NULL")->whereRaw("wu.phone_verification_code IS NULL")
			->where('wu.last_sms_tracking_reminder_notification_id', null)->where('wu.sms_notifications_enabled', true);
		if($debugUser){
			$phoneNumberObjectsQuery->where('wu.ID', $debugUser);
		}
		$phoneNumberObjects = $phoneNumberObjectsQuery->getArray();
		return $phoneNumberObjects;
	}
}
