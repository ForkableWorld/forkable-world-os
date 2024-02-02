<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Reminders;
class TrackingRemindersResponse {
	public $favorites = [];
	public $archived = [];
	public $active = [];
	/**
	 * TrackingRemindersResponse constructor.
	 * @param QMTrackingReminder[] $trackingRemindersArray
	 */
	public function __construct($trackingRemindersArray){
		foreach($trackingRemindersArray as $trackingReminder){
			if(!$trackingReminder->reminderFrequency){
				$this->favorites[] = $trackingReminder;
			} elseif(stripos($trackingReminder->valueAndFrequencyTextDescription, 'ended') !== false){
				$this->archived[] = $trackingReminder;
			} else{
				$this->active[] = $trackingReminder;
			}
		}
	}
}
