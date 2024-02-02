<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\IonIcon;
use App\UI\QMColor;
class SkipAllNotificationButton extends NotificationButton {
	/**
	 * @param QMTrackingReminderNotification $notification
	 */
	public function __construct($notification = null){
		$this->setBackgroundColor(QMColor::HEX_RED);
		$this->setIonIcon(IonIcon::help);
		$this->successToastText =
			"I'll skip all remaining " . $notification->getOrSetVariableDisplayName() . " notifications";
		$this->setTooltip("I don't remember");
		parent::__construct("Don't Remember", self::CALLBACK_skipAction, null, QMTrackingReminderNotification::SKIP_ALL,
			$notification);
	}
}
