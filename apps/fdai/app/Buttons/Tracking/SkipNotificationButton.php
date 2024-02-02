<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\IonIcon;
use App\UI\QMColor;
class SkipNotificationButton extends NotificationButton {
	/**
	 * @param QMTrackingReminderNotification $notification
	 */
	public function __construct($notification = null){
		$this->setIonIcon(IonIcon::androidCancel);
		$this->setBackgroundColor(QMColor::HEX_RED);
		$this->successToastText = "OK. We'll skip that one.";
		parent::__construct("Skip", self::CALLBACK_skipAction, null, QMTrackingReminderNotification::SKIP,
			$notification);
	}
}
