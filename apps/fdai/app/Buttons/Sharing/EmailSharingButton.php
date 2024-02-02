<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Sharing;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\IonIcon;
use App\UI\QMColor;
class EmailSharingButton extends SharingButton {
	public $fontAwesome = FontAwesome::EMAIL;
	/**
	 * EmailSharingButton constructor.
	 * @param string $linkToShare
	 * @param string $subjectLine
	 * @param string $bodyText
	 * @param bool $includeText
	 */
	public function __construct(string $linkToShare, string $subjectLine, string $bodyText, bool $includeText = true){
		$text = $includeText ? "Email" : "";
		$subjectLine = QMStr::truncate($subjectLine, 77, "...");
		$bodyText = QMStr::truncate($bodyText, 140, "...");
		parent::__construct($text, self::getEmailShareLink($linkToShare, $subjectLine, $bodyText),
			QMColor::HEX_FACEBOOK_BLUE, IonIcon::ion_icon_mail);
		$this->setAdditionalInformationAndTooltip("Share via Email");
		$this->setAction("share");
	}
	/**
	 * @param string $linkToShare
	 * @param string $subjectLine
	 * @param string $bodyText
	 * @return string
	 */
	public static function getEmailShareLink(string $linkToShare, string $subjectLine, string $bodyText): string{
		$body = rawurlencode($bodyText) . rawurlencode($linkToShare) . '%0A%0AHave%20a%20great%20day!';
		$subject = rawurlencode($subjectLine);
		return "mailto:?subject=$subject&body=$body";
	}
}
