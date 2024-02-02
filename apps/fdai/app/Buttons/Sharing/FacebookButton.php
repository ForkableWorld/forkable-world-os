<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Sharing;
use App\UI\FontAwesome;
use App\UI\IonIcon;
use App\UI\QMColor;
class FacebookButton extends SharingButton {
	public $fontAwesome = FontAwesome::FACEBOOK;
	public const LOGO_IMAGE = "https://www.filepicker.io/api/file/cvmSPOdlRaWQZnKFnBGt";
	public const PAGE_LINK = "https://www.facebook.com/Quantimodology";
	/**
	 * FacebookButton constructor.
	 * @param null $link
	 * @param null $additionalInfo
	 * @param bool $includeText
	 */
	public function __construct($link = null, $additionalInfo = null, bool $includeText = true){
		$text = $includeText ? "Facebook" : "";
		parent::__construct($text);
		$this->image = self::LOGO_IMAGE;
		$this->link = $link ?: self::PAGE_LINK;
		$this->ionIcon = IonIcon::ion_icon_facebook;
		$this->backgroundColor = QMColor::HEX_FACEBOOK_BLUE;
		$this->setAdditionalInformationAndTooltip($additionalInfo ?: "Follow us Facebook");
	}
}
