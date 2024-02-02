<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class PrivacyPolicyButton extends QMButton {
	public $title = "Privacy Policy";
	public $text = "Privacy Policy";
	public $tooltip = "Privacy Policy";
	public $link = "https://quantimo.do/privacy";
	public $fontAwesome = FontAwesome::PRIVACY;
	public $image = ImageUrls::ESSENTIAL_COLLECTION_LOCK;
}
