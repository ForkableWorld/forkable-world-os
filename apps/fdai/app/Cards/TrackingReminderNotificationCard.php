<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Buttons\QMButton;
use App\Buttons\Tracking\NoteNotificationButton;
use App\Buttons\Tracking\SkipAllNotificationButton;
use App\InputFields\InputField;
use App\InputFields\NumberInputField;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Types\QMArr;
use App\UI\IonIcon;
class TrackingReminderNotificationCard extends UserRelatedQMCard {
	protected $trackingReminderNotification;
	public $variableId;
	public $type = QMCard::TYPE_tracking_reminder_notification;
	/**
	 * @param $n
	 */
	public function __construct(QMTrackingReminderNotification $n = null){
		if(!$n){
			$n = QMTrackingReminderNotification::getMostRecent(QMAuth::getQMUser()->id);
		}
		$this->trackingReminderNotification = $n;
		$this->variableId = $n->variableId;
		$this->setAvatar($n->getImageUrl());
		$question = $n->getShortQuestion();
		$this->setHeaderTitle($question);
		$when = $n->getTrackingReminderNotificationTimeLocalHumanString();
		$when = ucfirst($when);
		$this->setSubHeader($when);
		$this->setNotificationButtons();
		$this->getParameters();
		$this->setNotificationInputFields();
		$this->setFallbackProperties($n, [
			'title',
			'textButtons',
			'iconButtons',
			'buttons',
		]);
		$id = $n->getId();
		parent::__construct($id, $n->getUserId());
		if(empty($this->avatar)){
			le('empty($this->avatar)');
		}
	}
	/**
	 * @return array
	 */
	public function getParameters(): array{
		$trackingReminderNotification = $this->getTrackingReminderNotification();
		$this->addParameter('variableName', $trackingReminderNotification->getVariableName());
		$this->addParameter('variableDisplayName', $trackingReminderNotification->getOrSetVariableDisplayName());
		$this->addParameter('trackingReminderNotificationId', $trackingReminderNotification->getId());
		$this->addParameter('trackingReminderId', $trackingReminderNotification->getTrackingReminderId());
		$this->addParameter('trackingReminderNotificationTimeEpoch',
			$trackingReminderNotification->getTrackingReminderNotificationTime());
		$this->addParameter('variableId', $trackingReminderNotification->getVariableIdAttribute());
		$this->addParameter('variableCategoryId', $trackingReminderNotification->getQMVariableCategory()->getId());
		$this->addParameter('variableCategoryName', $trackingReminderNotification->getQMVariableCategory()->getNameAttribute());
		return $this->parameters;
	}
	/**
	 * @return void
	 */
	private function setNotificationButtons(): void{
		$trackingReminderNotification = $this->getTrackingReminderNotification();
		$actionSheetButtons = $trackingReminderNotification->getActionSheetButtons();
		$this->buttonsSecondary = [
			new SkipAllNotificationButton($this->getTrackingReminderNotification()),
			new NoteNotificationButton($this->getTrackingReminderNotification()),
		];
		$this->setActionSheetButtons($actionSheetButtons);
		$NotificationActionButtons = $trackingReminderNotification->getNotificationActionButtons();
		if($trackingReminderNotification->isRating()){
			QMArr::sortAscending($NotificationActionButtons, 'modifiedValue');
		}
		$this->buttons = $NotificationActionButtons;
	}
	private function setNotificationInputFields(){
		$trackingReminderNotification = $this->getTrackingReminderNotification();
		$inputType = $trackingReminderNotification->getInputType();
		$inputField = $this->getModifiedValueInputField();
		if($inputType === QMUnit::INPUT_TYPE_value || $inputType === QMUnit::INPUT_TYPE_slider){
			$inputField->setShow(true);
		} else{
			$inputField->setShow(false);  // Need to be provided so the robot knows what to ask
		}
		$this->addInputField($inputField);
	}
	/**
	 * @return QMTrackingReminderNotification
	 */
	public function getTrackingReminderNotification(): QMTrackingReminderNotification{
		return $this->trackingReminderNotification;
	}
	/**
	 * @return InputField
	 */
	protected function getModifiedValueInputField(){
		$trackingReminderNotification = $this->getTrackingReminderNotification();
		$unit = $trackingReminderNotification->getUserUnit();
		if($trackingReminderNotification->isYesNoOrCountWithOnlyOnesAndZeros()){
			$unit = QMUnit::getYesNo();
		}
		$field = new NumberInputField("Other Value (" . $unit->abbreviatedName . ")", 'modifiedValue');
		$field->setRequired(true);
		$field->setUnit($unit);
		if($trackingReminderNotification->maximumAllowedValue !== null){
			$field->setMaxValue($trackingReminderNotification->maximumAllowedValue);
		}
		if($trackingReminderNotification->minimumAllowedValue !== null){
			$field->setMinValue($trackingReminderNotification->minimumAllowedValue);
		}
		$field->setSubmitButton($this->getSubmitButton());
		$field->setHelpText($trackingReminderNotification->getLongQuestion(null));
		$field->setHintUsingButtons($this->getButtons());
		return $field;
	}
	/**
	 * @return QMButton
	 */
	private function getSubmitButton(): QMButton{
		$submitButton = new QMButton("Record", null, null, IonIcon::androidSend);
		$submitButton->setParameters([
			'value' => null,
			'unitAbbreviatedName' => $this->getTrackingReminderNotification()->getUnitAbbreviatedName(),
		]);
		return $submitButton;
	}
}
