<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\HasModel;
use App\Buttons\QMButton;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NotFoundException;
use App\Models\BaseModel;
use App\Models\TrackingReminder;
use App\Properties\BaseProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\Reminders\QMTrackingReminder;
trait HasTrackingReminder {
	use HasUserVariable, HasVariableCategory;
	public function getTrackingReminderId(): int{
		$nameOrId = $this->getAttribute('tracking_reminder_id');
		return $nameOrId;
	}
	public function getTrackingReminderButton(): QMButton{
		$trackingReminder = $this->getQMTrackingReminder();
		if($trackingReminder){
			return $trackingReminder->getButton();
		}
		return TrackingReminder::generateDataLabShowButton($this->getTrackingReminderId());
	}
	/**
	 * @return QMTrackingReminder
	 * @throws NotFoundException
	 */
	public function getQMTrackingReminder(): QMTrackingReminder{
		if(property_exists($this, 'trackingReminder') && $this->trackingReminder){
			/** @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->trackingReminder;
		}
		if($fromMem = QMTrackingReminder::findInMemory($this->getTrackingReminderId())){
			return $fromMem;
		}
		return $this->getTrackingReminder()->getDBModel();
	}
	public function getTrackingReminderNameLink(): string{
		return $this->getQMTrackingReminder()->getDataLabDisplayNameLink();
	}
	public function getTrackingReminderImageNameLink(): string{
		return $this->getQMTrackingReminder()->getDataLabImageNameLink();
	}
	/**
	 * @return TrackingReminder
	 * @throws NotFoundException
	 */
	public function getTrackingReminder(): TrackingReminder{
		if($this instanceof BaseProperty && $this->parentModel instanceof TrackingReminder){
			return $this->parentModel;
		}
		/** @var BaseModel|DBModel $this */
		/** @var TrackingReminder $tr */
		if($tr = $this->getRelationIfLoaded('tracking_reminder')){
			if($v = $this->getRelationIfLoaded('variable')){
				$tr->setRelationAndAddToMemory('variable', $v);
			}
			if($uv = $this->getRelationIfLoaded('user_variable')){
				$tr->setRelationAndAddToMemory('user_variable', $uv);
			}
			return $tr;
		}
		$id = $this->getTrackingReminderId();
		$trackingReminder = TrackingReminder::findInMemoryOrDB($id);
		if(property_exists($this, 'relations')){
			$this->relations['tracking_reminder'] = $trackingReminder;
		}
		if(property_exists($this, 'trackingReminder')){
			$this->trackingReminder = $trackingReminder;
		}
		if(!$trackingReminder){
			throw new NotFoundException("Tracking reminder with: $id not found
			for this notification: $this->id!");
		}
		return $trackingReminder;
	}
	/**
	 * @return bool
	 */
	public function isEnabled(): bool{
		return $this->getTrackingReminder()->isEnabled();
	}
	/**
	 * @return bool
	 */
	public function hasEnded(): bool{
		return $this->getTrackingReminder()->hasEnded();
	}
	/**
	 * @return bool
	 */
	public function hasNotStarted(): bool{
		return $this->getTrackingReminder()->hasNotStarted();
	}
}
