<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
class TrackAllNotificationButton extends NotificationButton {
	/**
	 * @param NotificationButton $button
	 */
	public function __construct(NotificationButton $button, $notification){
		parent::__construct($button->text . ' for all', $button->callback, $button->modifiedValue,
			QMTrackingReminderNotification::TRACK_ALL, $notification);
	}
}
