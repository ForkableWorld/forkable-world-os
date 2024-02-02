<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Buttons\Message\EmailButton;
use App\UI\HtmlHelper;
use App\UI\IonIcon;
class HelpQMCard extends QMCard {
	/**
	 * HelpCard constructor.
	 */
	public function __construct(){
		parent::__construct();
		$this->setTitle("Need any help?");
		$this->setUrl(HtmlHelper::getHelpLinkAnchorHtml());
		$this->addButton(EmailButton::instance());
		$this->setIonIcon(IonIcon::help);
	}
}
