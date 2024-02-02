<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Vote;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class UsefulButton extends QMButton {
	public $fontAwesome = FontAwesome::THUMBS_UP;
	public function __construct(){
		parent::__construct("Useful?", null, null, IonIcon::thumbsup);
	}
}
