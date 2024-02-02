<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\AppSettings\AdditionalSettings\AppImages;
use App\AppSettings\AdditionalSettings\DownloadLinks;
use App\AppSettings\AppSettings;
use App\Buttons\QMButton;
use App\Buttons\Tracking\NotificationButton;
use App\Logging\QMLog;
use App\Slim\Model\StaticModel;
use App\Slim\Model\User\QMUser;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
class PushNotificationData extends StaticModel {
	private $deviceTokenObject;
	public $acknowledge;
	public $actions;
	public $color = "#2196F3";
	public $forceStart;
	public $image = '';
	public $isBackground = true;  // Trying true to prevent app from opening
	public $message;
	public $msgcnt;
	public $notId;
	public $soundName = false;
	public $title;
	public $url;
	public $user;
	/**
	 * PushNotificationData constructor.
	 * @param QMDeviceToken|null $deviceTokenObject
	 */
	public function __construct(QMDeviceToken $deviceTokenObject = null){
		if($deviceTokenObject){
			$this->setQMDeviceToken($deviceTokenObject);
		}
	}
	/**
	 * @param string $title
	 */
	public function setTitle(string $title){
		if(stripos($title, '(count)') !== false){
			$this->logError("Why is (count) in title $title?");
		}
		$this->title = $title;
	}
	/**
	 * @param bool $isBackground
	 */
	public function setIsBackground(bool $isBackground){
		$this->isBackground = $isBackground;
	}
	/**
	 * @param mixed $message
	 */
	public function setMessage(string $message){
		$message = strip_tags($message);
		$this->message = $message;
	}
	/**
	 * @param int $notId
	 */
	public function setNotId(int $notId){
		$this->notId = $notId;
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		/** @var QMDeviceToken $t */
		$t = $this->deviceTokenObject;
		$url = $this->url;
		if($t){
			if($clientId = $t->getClientId()){
				$url = UrlHelper::addParam($url, 'clientId', $clientId);
			}
			if($t->isAndroid() && stripos($url, 'https://') !== false){
				$url = QMStr::after("/#/app", $url);
				$url = "file:///android_asset/www/index.html#/app" . $url;
			}
		}
		return $this->url = $url;
	}
	/**
	 * @return string
	 */
	protected function setInboxUrl(): string{
		return $this->setUrl(IonicHelper::getInboxUrl([]));
	}
	/**
	 * @param string $url
	 * @return string
	 */
	public function setUrl(string $url): string{
		if($this->deviceTokenObject){
			$url = UrlHelper::addParam($url, 'clientId', $this->getDeviceTokenObject()->getClientId());
		}
		return $this->url = $url;
	}
	/**
	 * @return int
	 */
	public function getForceStart(): ?int{
		return $this->forceStart;
	}
	/**
	 * @param int $forceStart
	 */
	public function setForceStart(int $forceStart){
		$this->forceStart = $forceStart;
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		return $this->image;
	}
	/**
	 * @param string $image
	 */
	public function setImage(string $image){
		$this->image = $image;
	}
	/**
	 * @return string
	 */
	public function getMessage(): string{
		return $this->message;
	}
	/**
	 * @return string
	 */
	public function getMessageWithLink(): string{
		return HtmlHelper::getLinkAnchorHtml($this->message, $this->getUrl());
	}
	/**
	 * @return string
	 */
	public function getTitleAttribute(): string{
		return $this->title;
	}
	/**
	 * @return int
	 */
	public function getNotId(): int{
		return $this->notId;
	}
	/**
	 * @param QMDeviceToken $dt
	 */
	public function setQMDeviceToken(QMDeviceToken $dt){
		$this->deviceTokenObject = $dt;
		//$this->collapseKey = "msg1"; // Messes up the title
		$this->user = $dt->userId;
		$this->msgcnt = $dt->numberOfWaitingTrackingReminderNotifications;
		if(!$dt->requireAcknowledgement()){
			$this->acknowledge = true;
		}
	}
	/**
	 * @return QMDeviceToken
	 */
	public function getDeviceTokenObject(): QMDeviceToken{
		return $this->deviceTokenObject;
	}
	/**
	 * @return NotificationButton[]
	 */
	public function getActions(): array{
		return $this->actions;
	}
	/**
	 * @param NotificationButton[]|QMButton[] $actions
	 */
	public function setActions(array $actions): void{
		$this->actions = $actions;
	}
	/**
	 * @param NotificationButton $action
	 */
	public function addAction(NotificationButton $action){
		$this->actions[] = $action;
	}
	/**
	 * @return AppSettings
	 */
	protected function getAppSettings(): AppSettings{
		return $this->getDeviceTokenObject()->getClientAppSettings();
	}
	/**
	 * @return AppImages
	 */
	protected function getAppImages(): AppImages{
		return $this->getAppSettings()->getAdditionalSettings()->getAppImages();
	}
	/**
	 * @return AppImages
	 */
	protected function getIcon(): string{
		return $this->getAppImages()->appIcon;
	}
	/**
	 * @return DownloadLinks
	 */
	protected function getLinks(): DownloadLinks{
		return $this->getAppSettings()->getAdditionalSettings()->getDownloadLinks();
	}
	/**
	 * @return string
	 */
	public function getLogMetaDataString(): string{
		$str = $this->title ?? '';
		$token = $this->deviceTokenObject;
		if($token){
			$str .= " " . $token->platform . " push to " . $this->getUser()->loginName . " (" .
				substr($token->deviceToken, 0, 4) . "... last notified " .
				TimeHelper::timeSinceHumanString($token->lastNotifiedAt) . "): ";
		}
		return $str;
	}
	/**
	 * @return QMUser
	 */
	public function getUser(): ?QMUser{
		if($this->deviceTokenObject){
			return $this->getDeviceTokenObject()->getQMUser();
		}
		return null;
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->getDeviceTokenObject()->userId;
	}
	/**
	 * @return array
	 */
	public function compressAndPrepareForDelivery(): array{
		$clone = clone $this;
		$clone->unsetNullAndEmptyStringFields();
		$array = $clone->toArray();
		if($this->tooBig($array)){
			$clone->unsetFieldsWithHtmlInName();
			/** @var QMButton $action */
			foreach($clone->actions as $action){
				unset($action->stateParams);
				foreach($action as $key => $value){
					if(stripos($key, 'color') !== false ||
					   stripos($key, 'image') !== false || 
					   stripos($key, 'icon') !== false){
						unset($action->$key);
					}
				}
			}
			$array = $clone->toArray();
		}
		$this->validateSize($array);
		return $array;
	}
	/**
	 * @param array $array
	 */
	private function validateSize(array $array){
		if($this->tooBig($array)){
			ObjectHelper::logPropertySizes("push data", $array, false);
			QMLog::error(QMStr::prettyJsonEncode($array));
			le("Maximum size of push data is 4078 bytes!");
		}
	}
	/**
	 * @param array $array
	 * @return bool
	 */
	private function tooBig(array $array): bool{
		$sizeOfData = ObjectHelper::getSizeInKiloBytes($array);
		return $sizeOfData > 3.5;
	}
	/**
	 * @return string
	 */
	public function getTokenTitleUniqueId(): string{
		return $this->getDeviceTokenObject()->getDeviceTokenString() . "_" . $this->getTitleAttribute();
	}
}
