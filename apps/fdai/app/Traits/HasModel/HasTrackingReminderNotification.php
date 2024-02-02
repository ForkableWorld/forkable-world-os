<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Models\BaseModel;
use App\Models\TrackingReminderNotification;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
trait HasTrackingReminderNotification {
	public function getTrackingReminderNotificationId(): int{
		$nameOrId = $this->getAttribute('tracking_reminder_notification_id');
		return $nameOrId;
	}
	public function getTrackingReminderNotificationButton(): QMButton{
		$trackingReminderNotification = $this->getTrackingReminderNotification();
		if($trackingReminderNotification){
			return $trackingReminderNotification->getButton();
		}
		return TrackingReminderNotification::generateDataLabShowButton($this->getTrackingReminderNotificationId());
	}
	/**
	 * @return TrackingReminderNotification
	 */
	public function getTrackingReminderNotification(): TrackingReminderNotification{
		if($this instanceof BaseProperty && $this->parentModel instanceof TrackingReminderNotification){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		if($l = $this->getRelationIfLoaded('tracking_reminder_notification')){
			return $l;
		}
		$id = $this->getTrackingReminderNotificationId();
		$trackingReminderNotification = TrackingReminderNotification::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['tracking_reminder_notification'] = $trackingReminderNotification;
		}
		if(property_exists($this, 'trackingReminderNotification')){
			$this->trackingReminderNotification = $trackingReminderNotification;
		}
		return $trackingReminderNotification;
	}
	public function getTrackingReminderNotificationNameLink(): string{
		return $this->getTrackingReminderNotification()->getDataLabDisplayNameLink();
	}
	public function getTrackingReminderNotificationImageNameLink(): string{
		return $this->getTrackingReminderNotification()->getDataLabImageNameLink();
	}
}
