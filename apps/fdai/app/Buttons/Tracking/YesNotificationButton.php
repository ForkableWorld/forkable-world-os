<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\IonIcon;
use App\UI\QMColor;
class YesNotificationButton extends NotificationButton {
	/**
	 * YesNotificationButton constructor.
	 * @param QMTrackingReminderNotification $notification
	 */
	public function __construct($notification = null){
		$this->setBackgroundColor(QMColor::HEX_ARMY_GREEN);
		$this->setIonIcon(IonIcon::checkmark);
		parent::__construct("Yes", self::CALLBACK_trackYesAction, 1, QMTrackingReminderNotification::TRACK,
			$notification);
	}
}
