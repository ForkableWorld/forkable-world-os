<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\IonIcon;
use App\UI\QMColor;
class NoNotificationButton extends NotificationButton {
	/**
	 * @param QMTrackingReminderNotification $notification
	 */
	public function __construct($notification = null){
		$this->setBackgroundColor(QMColor::HEX_RED);
		$this->setIonIcon(IonIcon::androidCancel);
		parent::__construct("No", self::CALLBACK_trackNoAction, 0, QMTrackingReminderNotification::TRACK,
			$notification);
	}
}
