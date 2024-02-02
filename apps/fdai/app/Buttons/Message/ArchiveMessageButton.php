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
class ArchiveMessageButton extends QMButton {
	public const ACTION = 'archive';
	/**
	 * @param MessagesMessage $message
	 */
	public function __construct($message = null){
		$this->successToastText = "Message archived";
		$this->setWebhookUrl(UrlHelper::getApiUrlForPath('messages', [
			'messageId' => $message->getId(),
			'action' => self::ACTION,
		]));
		parent::__construct("Archive", null, QMColor::HEX_PURPLE, IonIcon::archive);
	}
}
