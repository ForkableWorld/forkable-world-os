<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */
namespace App\Slim\Model\Notifications;
use App\Files\FileHelper;
use App\Logging\QMLog;
use App\Notifications\ApnsPHP\ApnsPHP_Abstract;
use App\Notifications\ApnsPHP\ApnsPHP_Exception;
use App\Notifications\ApnsPHP\ApnsPHP_Message;
use App\Notifications\ApnsPHP\ApnsPHP_Push;
use App\Notifications\ApnsPHP\Message\ApnsPHP_Message_Exception;
use App\Notifications\ApnsPHP\Push\ApnsPHP_Push_Exception;
class ApplePushNotification extends PushNotification {
	private ApnsPHP_Push $ApnsPHP_Push;
	/**
	 * @param QMDeviceToken $row
	 * @param PushNotificationData $pushData
	 */
	public function __construct(QMDeviceToken $row, PushNotificationData $pushData){
		parent::__construct($row, $pushData);
		try {
			$this->sendPush();
		} catch (ApnsPHP_Message_Exception | ApnsPHP_Push_Exception | ApnsPHP_Exception $e) {
			$this->setErrorMessage(__METHOD__.": ".$e->getMessage());
		}
	}
	private function setResponse(){
		$r = $this->response = new ApplePushNotificationResponse($this->getApnsPHPPush());
		$this->setSuccess($r->success);
		$this->setErrorMessage($r->error);
	}
	/**
	 * @return string
	 */
	private function getAppCertPath(): string{
		$path = FileHelper::projectRoot() . "/configs/ios/push_notifications/production_";
		$providerCertificateFile = $path . "com.quantimodo.moodimodoapp.pem";
		if($this->getDeviceTokenObject()->appIdentifier){
			$providerCertificateFile = $path . $this->getDeviceTokenObject()->appIdentifier . ".pem";
		}
		return $providerCertificateFile;
	}
	/**
	 * @throws ApnsPHP_Exception
	 */
	private function setApplePush(){
		// Using Autoload all classes are loaded on-demand
		$appCert = $this->getAppCertPath();
		$this->logDebug("Using Apple push certificate: $appCert. ");
		$this->ApnsPHP_Push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION, $appCert);
		$this->ApnsPHP_Push->setLogger(new ApplePushLogger());
		// Set the Provider Certificate passphrase
        if(!\App\Utils\Env::get('APPLE_PUSH_PASSPHRASE')){
            le('APPLE_PUSH_PASSPHRASE environment variable not set!');
        }
		$this->ApnsPHP_Push->setProviderCertificatePassphrase(\App\Utils\Env::get('APPLE_PUSH_PASSPHRASE'));
		// Set the Root Certificate Authority to verify the Apple remote peer
		$this->ApnsPHP_Push->setRootCertificationAuthority($this->rootCertPath());
		QMLog::debug("Connecting to the Apple Push Notification Service...");
		$this->ApnsPHP_Push->connect();
	}
	/**
	 * @return ApnsPHP_Message
	 * @throws ApnsPHP_Message_Exception
	 */
	private function getAppleMessage(): ApnsPHP_Message{
		// Instantiate a new Message with a single recipient
		$appleMessage = new ApnsPHP_Message($this->getDeviceTokenObject()->deviceToken);
		// Set a custom identifier. To get back this identifier use the getCustomIdentifier() method
		// over a ApnsPHP_Message object retrieved with the getErrors() message.
		$appleMessage->setCustomIdentifier("Message-Badge-3");
		// Set badge icon to "3"
		$appleMessage->setBadge(1);
		//$message->setBadge($deviceTokenObject->number_of_waiting_tracking_reminder_notifications);
		$appleMessage->setText($this->getPushData()->message);
		$appleMessage->setSound($this->getPushData()->soundName); // Play the default sound
		// setContentAvailable makes plugin calls all of your notification event handlers
		// i.e. qmLocationService.updateLocationVariablesAndPostMeasurementIfChanged() &
		//  reminderService.refreshTrackingReminderNotifications()
		// See https://github.com/phonegap/phonegap-plugin-push/blob/2660b51da66e791ff342d027ea6afa4313281e28/docs/PAYLOAD.md#background-notifications
		$appleMessage->setContentAvailable(true);
		$appleMessage->setCustomProperty('notId', $this->getPushData()->getNotId());
		if($this->getDeviceTokenObject()->requireAcknowledgement()){
			$appleMessage->setCustomProperty('acknowledge', true);
		}
		// Set a custom property
		//$message->setCustomProperty('acme2', array('bang', 'whiz'));
		// Set another custom property
		//$message->setCustomProperty('acme3', array('bing', 'bong'));
		// Set the expiry value to 1 hour (hopefully, this will prevent/stack duplicate notifications)
		$appleMessage->setExpiry(3600);
		$this->logDebug("Apple push info: " . \App\Logging\QMLog::print_r($appleMessage, true));
		return $appleMessage;
	}
	/**
	 * @return ApnsPHP_Push
	 */
	public function getApnsPHPPush(): ApnsPHP_Push{
		return $this->ApnsPHP_Push;
	}
	/**
	 * @throws ApnsPHP_Exception
	 * @throws ApnsPHP_Message_Exception
	 * @throws ApnsPHP_Push_Exception
	 */
	private function sendPush(): void{
		$this->setApplePush();
		$p = $this->getApnsPHPPush();
		$p->add($this->getAppleMessage());  // Add the message to the message queue
		$this->logInfo("Sending " . $this->getUser()->loginName . " Apple push");
		$p->send();  // Send all messages in the message queue
		$p->disconnect();  // Disconnect from the Apple Push Notification Service
		$this->setResponse();
	}
	/**
	 * @return string
	 */
	private function rootCertPath(): string{
		return FileHelper::projectRoot() . "/configs/ios/push_notifications/entrust_root_certification_authority.pem";
	}
}
