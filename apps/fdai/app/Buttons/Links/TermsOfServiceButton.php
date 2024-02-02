<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class TermsOfServiceButton extends QMButton {
	public $title = "Terms of Service";
	public $text = "Terms of Service";
	public $tooltip = "Terms of Service";
	public $link = "https://quantimo.do/tos";
	public $fontAwesome = FontAwesome::FILE_CONTRACT_SOLID;
	public $image = ImageUrls::BASIC_FLAT_ICONS_CONTRACT;
}
