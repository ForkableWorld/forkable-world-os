<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class StudiesButton extends QMButton {
	public $title = "Studies";
	public $link = "https://studies.quantimo.do";
	public $image = ImageUrls::SCIENCE_ATOM;
	public $fontAwesome = FontAwesome::FLASK_SOLID;
	public $tooltip = "The Journal of Citizen Science";
}
