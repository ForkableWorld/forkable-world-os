<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\TrackingReminderNotification;
use App\Traits\HasModel\HasTrackingReminderNotification;
trait TrackingReminderNotificationProperty {
	use HasTrackingReminderNotification;
	public function getTrackingReminderNotificationId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getTrackingReminderNotification(): TrackingReminderNotification{
		return $this->getParentModel();
	}
}
