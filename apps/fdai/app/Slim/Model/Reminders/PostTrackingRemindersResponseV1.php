<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Reminders;
class PostTrackingRemindersResponseV1 {
	public $trackingReminderNotifications;
	public $trackingReminders;
	public $userVariables;
	public $status;
	public $success;
	/**
	 * PostTrackingRemindersResponseV1 constructor.
	 * @param $responseV3
	 */
	public function __construct($responseV3){
		$this->status = 201;
		$this->success = true;
		if(!isset($responseV3['data'])){
			return;
		}
		if(isset($responseV3['data']['trackingReminderNotifications'])){
			$this->trackingReminderNotifications = $responseV3['data']['trackingReminderNotifications'];
		}
		if(isset($responseV3['data']['trackingReminders'])){
			$this->trackingReminders = $responseV3['data']['trackingReminders'];
		}
		if(isset($responseV3['data']['userVariables'])){
			$this->trackingReminderNotifications = $responseV3['data']['userVariables'];
		}
	}
}
