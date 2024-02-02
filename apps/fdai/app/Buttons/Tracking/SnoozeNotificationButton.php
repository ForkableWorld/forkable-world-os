<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\IonIcon;
use App\UI\QMColor;
class SnoozeNotificationButton extends NotificationButton {
	/**
	 * @param QMTrackingReminderNotification $notification
	 */
	public function __construct($notification = null){
		$this->setBackgroundColor(QMColor::HEX_PURPLE);
		$this->setIonIcon(IonIcon::androidNotificationsOff);
		$this->successToastText = "I'll remind you again in an hour";
		parent::__construct("Snooze", self::CALLBACK_snoozeAction, null, QMTrackingReminderNotification::SNOOZE,
			$notification);
	}
}
