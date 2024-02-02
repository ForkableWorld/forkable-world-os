<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Message;
use App\Buttons\QMButton;
use App\Slim\Model\MessagesMessage;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\UrlHelper;
class SnoozeMessageButton extends QMButton {
	public const ACTION = 'snooze';
	/**
	 * @param MessagesMessage $message
	 */
	public function __construct($message = null){
		$this->setBackgroundColor(QMColor::HEX_PURPLE);
		$this->successToastText = "I'll remind you again in an hour";
		$this->setWebhookUrl(UrlHelper::getApiUrlForPath('messages', [
			'messageId' => $message->getId(),
			'action' => NotificationButton::CALLBACK_snoozeAction,
		]));
		parent::__construct("Snooze", null, null, IonIcon::androidNotificationsOff);
	}
}
