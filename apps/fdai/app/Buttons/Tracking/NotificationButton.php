<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Buttons\QMButton;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminder;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
class NotificationButton extends QMButton {
	public const CALLBACK_trackOneRatingAction = 'trackOneRatingAction';
	public const CALLBACK_trackTwoRatingAction = 'trackTwoRatingAction';
	public const CALLBACK_trackThreeRatingAction = 'trackThreeRatingAction';
	public const CALLBACK_trackFourRatingAction = 'trackFourRatingAction';
	public const CALLBACK_trackDefaultValueAction = 'trackDefaultValueAction';
	public const CALLBACK_snoozeAction = 'snoozeAction';
	public const CALLBACK_skipAction = 'skipAction';
	public const CALLBACK_skipAllAction = 'skipAllAction';
	public const CALLBACK_trackLastValueAction = 'trackLastValueAction';
	public const CALLBACK_trackSecondToLastValueAction = 'trackSecondToLastValueAction';
	public const CALLBACK_trackThirdToLastValueAction = 'trackThirdToLastValueAction';
	public const CALLBACK_trackZeroAction = 'trackZeroAction';
	public const CALLBACK_trackYesAction = 'trackYesAction';
	public const CALLBACK_trackNoAction = 'trackNoAction';
	public $longTitle;
	public $callback;
	public $modifiedValue;
	public $action;
	public $foreground;
	public $shortTitle;
	public $image;
	private $unitName;
	/**
	 * @param QMUnit|string $unitOrTitle
	 * @param string $callback
	 * @param float $modifiedValue
	 * @param string $action
	 * @param QMTrackingReminderNotification|null $notification
	 */
	public function __construct($unitOrTitle, string $callback, $modifiedValue,
		string $action = QMTrackingReminderNotification::TRACK, $notification = null){
		$this->action = $action;
		$this->callback = $callback;
		$this->foreground = false;
		$this->modifiedValue = $modifiedValue;
		if($modifiedValue !== null && !$this->successToastText && $notification){
			$this->successToastText = "Recorded " . $notification->getUserUnit()->getValueAndUnitString($modifiedValue);
		}
		if(is_string($unitOrTitle)){
			$this->text = $this->shortTitle = $this->longTitle = $unitOrTitle;
		} elseif($unitOrTitle instanceof QMUnit){
			$this->unitName = $unitOrTitle->name;
			$this->setTitles();
		} else{
			le("unitOrTitle is $unitOrTitle");
		}
		$this->setTooltip($this->longTitle ?: $this->text ?: $this->shortTitle);
		parent::__construct($this->text);
		$this->addParameter('value', $modifiedValue);
		$this->addParameter('modifiedValue', $modifiedValue);
		$this->addParameter('action', $action);
		if($notification && isset($notification->trackingReminderNotificationId)){
			$this->addNotificationParameters($notification);
		}
		$this->setFunctionName($action);
		if(!$this->webhookUrl){
			$this->webhookUrl = $notification->getWebhookUrl();
		}
	}
	/**
	 * @param QMTrackingReminderNotification|QMTrackingReminder $n
	 */
	public function addNotificationParameters($n){
		$this->addParameter('unitAbbreviatedName', $n->getUserOrCommonUnit()->abbreviatedName);
		if(method_exists($n, 'getTrackingReminderNotificationId')){
			$this->addParameter('trackingReminderNotificationId', $n->getTrackingReminderNotificationId());
		}
	}
	/**
	 * @param QMTrackingReminderNotification $trackingReminderNotification
	 * @return NotificationButton
	 */
	public static function defaultValueAction(QMTrackingReminderNotification $trackingReminderNotification): NotificationButton{
		$defaultValueAction =
			new self($trackingReminderNotification->getUserUnit(), self::CALLBACK_trackDefaultValueAction,
				$trackingReminderNotification->defaultValue);
		return $defaultValueAction;
	}
	/**
	 * @return string
	 */
	public function setTitles(): string{
		$this->setShortTitle();
		$this->setLongTitle();
		if($this->isYesNo() || $this->isRating() || $this->getUnit()->isCountCategory()){
			return $this->text = $this->shortTitle;
		}
		return $this->text = $this->getValueAndUnitString();
	}
	/**
	 * @return string
	 */
	public function getCallback(): string{
		return $this->callback;
	}
	/**
	 * @param string $callback
	 */
	public function setCallback(string $callback){
		$this->callback = $callback;
	}
	/**
	 * @return float|null
	 */
	public function getModifiedValue(): ?float{
		return $this->modifiedValue;
	}
	/**
	 * @param float|null $modifiedValue
	 */
	public function setModifiedValue(?float $modifiedValue){
		$this->modifiedValue = $modifiedValue;
	}
	/**
	 * @return string
	 */
	public function getAction(): ?string{
		return $this->action;
	}
	/**
	 * @return string
	 */
	public function setShortTitle(): string{
		if($this->isYesNo()){
			if($this->modifiedValue){
				return $this->shortTitle = 'YES';
			}
			return $this->shortTitle = 'NO';
		}
		if($this->isRating()){
			return $this->shortTitle = $this->getValueAndUnitString();
		}
		return $this->shortTitle = (string)$this->modifiedValue;
	}
	/**
	 * @return string
	 */
	public function setLongTitle(): string{
		if($this->isRating()){
			return $this->longTitle = "Rate " . $this->getValueAndUnitString();
		}
		return $this->longTitle = "Record " . $this->getValueAndUnitString();
	}
	/**
	 * @return bool
	 */
	private function isRating(): bool{
		return $this->getUnit()->isRating();
	}
	/**
	 * @return string
	 */
	private function getValueAndUnitString(): string{
		return $this->getUnit()->getValueAndUnitString($this->modifiedValue, true);
	}
	/**
	 * @return bool
	 */
	private function isYesNo(): bool{
		return $this->getUnit()->isYesNo();
	}
	/**
	 * @return QMUnit
	 */
	private function getUnit(): QMUnit{
		$unitName = $this->unitName;
		$QMUnit = QMUnit::getByNameOrId($unitName);
		if(!$QMUnit){
			$QMUnit = QMUnit::getByNameOrId($unitName);
			le("could not find unit: $unitName");
		}
		return $QMUnit;
	}
}
