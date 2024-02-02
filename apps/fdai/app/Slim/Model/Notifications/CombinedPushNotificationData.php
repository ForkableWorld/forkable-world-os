<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Buttons\Tracking\NotificationButton;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
class CombinedPushNotificationData extends PushNotificationData {
	public const TITLE = 'How are you?';
	/**
	 * CombinedPushNotificationData constructor.
	 * @param QMDeviceToken $deviceTokenObject
	 */
	public function __construct(QMDeviceToken $deviceTokenObject){
		parent::__construct($deviceTokenObject);
		$this->setMessage(QMTrackingReminderNotification::getPushNotificationMessage($deviceTokenObject->getQMUser()));
		$this->setTitle(self::TITLE);
		$this->setNotId($deviceTokenObject->userId); // notId required to wake up app https://github.com/phonegap/phonegap-plugin-push/commit/2660b51da66e791ff342d027ea6afa4313281e28
		if(!$this->getIcon()){
			$this->logError("Could not get icon for " . $this->getAppSettings()->appDisplayName,
				['appSettings' => $this->getAppSettings()]);
		} else{
			$this->setImage($this->getIcon());
		}
		$this->addAction(new NotificationButton("Open Inbox", $this->getLinks()->getInboxUrl(), null, 'inbox'));
		$this->addAction(new NotificationButton("Notification Settings", $this->getLinks()->getSettingsUrl(), null,
			'settings'));
		$this->setInboxUrl();
	}
}
