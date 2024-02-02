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
use App\Utils\IonicHelper;
class ReplyMessageButton extends QMButton {
	/**
	 * @param MessagesMessage $message
	 */
	public function __construct($message = null){
		$this->setBackgroundColor(QMColor::HEX_PURPLE);
		$url = IonicHelper::getChatUrl(['message' => $message]);
		$this->setUrl($url);
		$this->setAction($url);
		parent::__construct("Reply", null, null, IonIcon::reply);
	}
}
