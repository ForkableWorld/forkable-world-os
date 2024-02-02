<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Exceptions\VariableCategoryNotFoundException;
use App\Logging\QMLog;
use App\Models\UserVariable;
use App\Properties\Base\BasePlatformProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Utils\AppMode;
use App\Utils\IonicHelper;
use App\Variables\CommonVariables\EmotionsCommonVariables\OverallMoodCommonVariable;
use App\Variables\QMVariableCategory;
/** Class IndividualPushNotificationData
 * @package App\Slim\Model\Notifications
 */
class IndividualPushNotificationData extends PushNotificationData {
	public $icon;
	public $lastValue;
	public $secondToLastValue;
	public $thirdToLastValue;
	public $trackingReminderNotificationId;
	public $unitAbbreviatedName;
	public $valence;
	public $variableCategoryId;
	public $variableDisplayName;
	public $variableName;
	/**
	 * IndividualPushNotificationData constructor.
	 * @param QMDeviceToken|null $dt
	 * @param UserVariable $r
	 */
	public function __construct(?QMDeviceToken $dt, $r){
		$r = $r->getUserVariable();
		$this->setUserVariable($r);
		parent::__construct($dt);
		$this->setInboxUrl();
		$this->validateButtons($r);
	}
	/**
	 * @param UserVariable $r
	 * @return void
	 */
	private function setActionsByUserVariable(UserVariable $r): void{
		$buttons = $r->getNotificationActionButtons();
		if(isset($buttons[0], $buttons[0]->modifiedValue)){
			$this->lastValue = $buttons[0]->modifiedValue;
		}
		if(isset($buttons[1], $buttons[1]->modifiedValue)){
			$this->secondToLastValue = $buttons[1]->modifiedValue;
		}
		if(isset($buttons[2], $buttons[2]->modifiedValue)){
			$this->thirdToLastValue = $buttons[2]->modifiedValue;
		}
		foreach($buttons as $key => $value){
			if($key > 2){
				unset($buttons[$key]);
			}
		} // More than 3 buttons are too large for payload
		$toSend = [];
		foreach($buttons as $button){
			$clone = clone $button;
			if(!isset($clone->title)){
				$clone->title = $clone->text;
				unset($clone->text);
			}
			$clone->unsetNullAndEmptyStringFields();
			$toSend[] = $clone;
		}
		// Why would we encode this?
		//$this->actions = json_encode($toSend);
		$this->actions = $toSend;
	}
	/**
	 * @return QMVariableCategory
	 * @throws VariableCategoryNotFoundException
	 */
	private function getQMVariableCategory(): QMVariableCategory{
		$id = $this->getVariableCategoryId();
		return QMVariableCategory::find($id);
	}
	/**
	 * @param QMDeviceToken|null $dt
	 * @throws VariableCategoryNotFoundException
	 */
	public function setQMDeviceToken(QMDeviceToken $dt): void{
		parent::setQMDeviceToken($dt);
		if($dt->platform === BasePlatformProperty::PLATFORM_ANDROID){
			$this->icon = 'ic_stat_icon_bw';
			$this->image = $this->getQMVariableCategory()->getPngUrl();
		}
		$this->getUrl();
	}
	/**
	 * @param UserVariable $v
	 */
	private function setUserVariable(UserVariable $v): void{
		$this->variableCategoryId = $v->getVariableCategoryId();
		$this->setMessage('Pull down and select a value to record or tap to open inbox for more options');
		$this->setTitle('Track ' . $v->getOrSetVariableDisplayName());
		//$this->trackingReminderNotificationId = $n->id;
		$this->valence = $v->getValence();
		$this->unitAbbreviatedName = $v->getUnitAbbreviatedName();
		$this->variableName = $v->getOrSetVariableDisplayName(); // TODO: Remove this after serviceworker is updated
		$this->variableDisplayName = $v->getOrSetVariableDisplayName();
		$this->setActionsByUserVariable($v);
		$this->icon = $v->getQMVariableCategory()->getPngUrl();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		if(!$this->url){
			$this->setUrl(IonicHelper::getInboxUrl([]));
		}
		return parent::getUrl();
	}
	/**
	 * @param UserVariable $r
	 */
	private function validateButtons(UserVariable $r): void{
		$this->exceptionIfYesNoWithNumberOfTitle($r);
		$this->exceptionIf2ndButtonSnoozeAndFirstNotRecordMeasurement($r);
		$this->exceptionIfFirstButtonSnooze($r);
		IndividualPushNotificationData::exceptionIfDuplicateButton($this->getActions());
		$this->checkMikeMoodButtons($r);
	}
	/**
	 * @param UserVariable $n
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	private function exceptionIf2ndButtonSnoozeAndFirstNotRecordMeasurement(UserVariable $n): void{
		$buttons = $this->getActions();
		$button1 = $buttons[0];
		/** @noinspection MultiAssignmentUsageInspection */
		$button2 = $buttons[1];
		if($button1->text !== "Record a Measurement" && $button2->action === QMTrackingReminderNotification::SNOOZE){
			$cv = $n->getCommonVariable();
			$mostCommon = $cv->getMostCommonValuesInCommonUnit();
			$uv = $n->getUserVariable();
			$lastValues = $uv->getLastValuesInUserUnit();
			le("2nd button should not be snooze!");
		}
	}
	/**
	 * @param UserVariable $n
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	private function exceptionIfFirstButtonSnooze(UserVariable $n): void{
		$buttons = $this->getActions();
		$button1 = $buttons[0];
		if($button1->action === QMTrackingReminderNotification::SNOOZE){
			$cv = $n->getCommonVariable();
			$mostCommon = $cv->getMostCommonValuesInCommonUnit();
			$uv = $n->getUserVariable();
			$lastValues = $uv->getLastValuesInUserUnit();
			le("1st button should not be snooze!");
		}
	}
	public static function exceptionIfDuplicateButton(array $buttons): void{
		$titles = [];
		foreach($buttons as $b){
			if(in_array($b->title, $titles)){
				le("$b->title already in", $titles);
			}
			$titles[] = $b->title;
		}
	}
	/**
	 * @param UserVariable $n
	 */
	private function exceptionIfYesNoWithNumberOfTitle(UserVariable $n): void{
		$buttons = $this->getActions();
		$button1 = $buttons[0];
		if((stripos($this->title, "Number of") !== false) && stripos($button1->text, "Yes") !== false){
			$this->setUserVariable($n);
			QMLog::exceptionIfTesting("$this->title should not have yes no buttons!");
		}
	}
	/**
	 * @param UserVariable $n
	 */
	private function checkMikeMoodButtons(UserVariable $n): void{
		$buttons = $this->getActions();
		$button2 = $buttons[1];
		if($n->getUserId() === UserIdProperty::USER_ID_MIKE && !AppMode::isUnitOrStagingUnitTest() &&
			$n->getVariableIdAttribute() === OverallMoodCommonVariable::ID){
			if($button2->modifiedValue == 5 || $button2->modifiedValue == 1){
				$this->logError("Mike second mood value should not be $button2->modifiedValue!");
			}
		}
	}
	/**
	 * @param int $trackingReminderNotificationId
	 */
	public function setTrackingReminderNotificationId(int $trackingReminderNotificationId): void{
		$this->setNotId($trackingReminderNotificationId);
		$this->trackingReminderNotificationId = $trackingReminderNotificationId;
	}
	public function getVariableCategoryId(): int{
		if($this->variableCategoryId){
			return $this->variableCategoryId;
		}
		if("no category id!"){
			le('no category id!');
		}
		throw new \LogicException();
	}
}
