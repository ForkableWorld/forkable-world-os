<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\FontAwesome;
use App\UI\IonIcon;
use App\UI\QMColor;
class AboveAverageNotificationButton extends NotificationButton {
	public $fontAwesome = FontAwesome::LEVEL_UP_ALT_SOLID;
	/**
	 * @param QMTrackingReminderNotification|null $notification
	 */
	public function __construct(QMTrackingReminderNotification $notification = null){
		$this->setBackgroundColor(QMColor::HEX_ARMY_GREEN);
		$this->setIonIcon(IonIcon::checkmark);
		parent::__construct("Above Average", self::CALLBACK_trackYesAction, 1, QMTrackingReminderNotification::TRACK,
			$notification);
	}
}
