<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Exceptions\InvalidDeviceTokenException;
use App\Exceptions\PushException;
use App\Slim\Model\User\QMUser;
use App\Traits\LoggerTrait;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\Utils\AppMode;
use LogicException;
abstract class PushNotification {
	use LoggerTrait;
	public const MINUTES_BETWEEN_CHECKS_FOR_NEW_NOTIFICATIONS = 15;
	protected $deviceTokenObject; // Must be protected to avoid Mongo recursion error
	public $errorMessage;
	public $pushData;
	public $response;
	public $success = true;
	public $user;
	/**
	 * PushNotification constructor.
	 * @param $row
	 * @param PushNotificationData $pushData
	 */
	public function __construct($row, $pushData){
		foreach($row as $key => $value){
			//$camelCaseKey = StringHelper::convertStringToCamelCaseIfSnakeCase($key);   Not sure why this doesn't work
			$camelCaseKey = QMStr::toCamelCase($key);
			$this->$camelCaseKey = $value;
		}
		if(!$row->deviceToken){
			throw new LogicException('No device token provided to sendApplePushNotification!', ['tokenObject' => $row]);
		}
		if($row->deviceToken == "null"){
			throw new LogicException('null string device token provided to sendApplePushNotification!',
				['tokenObject' => $row]);
		}
		$this->deviceTokenObject = $row;
		$this->pushData = $pushData;
	}
	/**
	 * @return QMDeviceToken
	 */
	public function getDeviceTokenObject(){
		return $this->deviceTokenObject;
	}
	/**
	 * @return PushNotificationData
	 */
	public function getPushData(){
		return $this->pushData;
	}
	/**
	 * @return bool
	 */
	public function getSuccess(){
		return $this->success;
	}
	/**
	 * @param bool $success
	 */
	public function setSuccess($success){
		$this->success = (bool)$success;
		if($success){
			$this->getDeviceTokenObject()->setErrorMessage(null);
		}
	}
	/**
	 * @return string
	 */
	public function getErrorMessage(){
		return $this->errorMessage;
	}
	/**
	 * @param string $errorMessage
	 */
	public function setErrorMessage($errorMessage){
		if($errorMessage === null){
			return;
		}
        $dt = $this->getDeviceTokenObject();
		if(stripos($errorMessage, "invalid or malformed FCM-Token") !== false){
			le("Make sure GOOGLE_CLOUD_MESSAGING_API_KEY does not contain quote marks!");
		}
		if(stripos($errorMessage, "INVALID_KEY") !== false){
			le("Make sure GOOGLE_CLOUD_MESSAGING_API_KEY does not contain quote marks! $errorMessage
                Key used: " . GooglePushNotification::getGoogleCloudMessagingKey());
		}
		if(AppMode::isTestingOrStaging()){
			if(stripos($errorMessage, "missing an Authentication Key") === false){
                $dt->deleteTestToken();
			}
			if(!AppMode::isTravisOrHeroku()){
				if($errorMessage === "NotRegistered"){
					throw new InvalidDeviceTokenException($errorMessage, $dt);
				}
				ObjectHelper::logPropertySizes("push data", $this->getPushData()->compressAndPrepareForDelivery());
				throw new PushException($errorMessage, $this);
			}
			$this->logError($errorMessage);
		}
		$this->setSuccess(false);
        $dt->setErrorMessage($errorMessage);
	}
	/**
	 * @return QMUser
	 */
	public function getUser(): ?QMUser{
		return $this->getDeviceTokenObject()->getQMUser();
	}
	/**
	 * @return PushNotificationResponse
	 */
	public function getResponse(): PushNotificationResponse
    {
        if(!is_object($this->response)){
            le("No response set for " . get_class($this));
        }
		return $this->response;
	}
	/**
	 * @return string
	 */
	public function __toString(){
		$token = $this->getDeviceTokenObject();
		return $this->getPushData()->getTitleAttribute() . " " . $token->platform . " push to " . $this->getUser()->loginName .
			" (" . substr($token->deviceToken, 0, 4) . "... last notified " .
			TimeHelper::timeSinceHumanString($token->lastNotifiedAt) . "): ";
	}
}
