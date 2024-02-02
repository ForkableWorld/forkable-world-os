<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Message;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class EmailButton extends QMButton {
	public $fontAwesome = FontAwesome::EMAIL;
	/**
	 * EmailButton constructor.
	 * @param string $email
	 * @param string $title
	 */
	public function __construct(string $email = "help@curedao.org", $title = "Email help@curedao.org"){
		parent::__construct();
		$this->setIonIcon(IonIcon::email);
		$url = "mailto:$email";
		$this->setUrl($url);
		$this->setTextAndTitle($title);
		$this->setTooltip("Email me! I'm lonely!");
	}
}
