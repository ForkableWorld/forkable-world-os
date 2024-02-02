<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Models\TrackingReminder;
use App\Traits\HasModel\HasTrackingReminder;
trait TrackingReminderProperty {
	use HasTrackingReminder;
	public function getTrackingReminderId(): int{
		return $this->getParentModel()->getId();
	}
	/** @noinspection PhpIncompatibleReturnTypeInspection */
	public function getTrackingReminder(): TrackingReminder{
		return $this->getParentModel();
	}
	public function getUserId(): ?int{
		return $this->getTrackingReminder()->getUserId();
	}
}
