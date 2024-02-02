<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class ContactUsButton extends QMButton {
	public $title = "Contact Us";
	public $link = 'mailto:help@curedao.org';
	public $image = ImageUrls::DIALOGUE_ASSETS_MESSENGER;
	public $fontAwesome = FontAwesome::FACEBOOK_MESSENGER;
	public $tooltip = "Contact Us";
}
