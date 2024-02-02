<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Buttons\States\MeasurementAddStateButton;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
class NoteNotificationButton extends MeasurementAddStateButton {
	public $ionIcon = IonIcon::edit;
	public $backgroundColor = QMColor::HEX_PURPLE;
	public $image = ImageUrls::EDUCATION_NOTES;
	/**
	 * @param QMTrackingReminderNotification $notification
	 */
	public function __construct($notification = null){
		$this->setParameters($notification->getUrlParams());
		parent::__construct();
		$this->setTextAndTitle("Add a note");
	}
}
