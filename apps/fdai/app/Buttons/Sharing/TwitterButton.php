<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Sharing;
use App\UI\FontAwesome;
use App\UI\IonIcon;
use App\UI\QMColor;
class TwitterButton extends SharingButton {
	public $fontAwesome = FontAwesome::TWITTER;
	public const LOGO_IMAGE = "https://www.filepicker.io/api/file/Gvu32apSQDqLMb40pvYe";
	public const PAGE_LINK = "https://twitter.com/quantimodo";
	/**
	 * TwitterButton constructor.
	 * @param null $link
	 * @param null $additionalInfo
	 * @param bool $includeText
	 */
	public function __construct($link = null, $additionalInfo = null, bool $includeText = true){
		$text = $includeText ? "Twitter" : "";
		parent::__construct($text);
		$this->ionIcon = IonIcon::ion_icon_twitter;
		$this->backgroundColor = QMColor::HEX_TWITTER_BLUE;
		$this->image = self::LOGO_IMAGE;
		$this->link = $link ?: self::PAGE_LINK;
		$this->setAdditionalInformationAndTooltip($additionalInfo ?: "Follow us on Twitter");
	}
}
