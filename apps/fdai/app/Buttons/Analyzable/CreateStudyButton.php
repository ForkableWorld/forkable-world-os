<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Analyzable;
use App\Buttons\QMButton;
use App\UI\IonIcon;
use App\UI\QMColor;
use App\Utils\IonicHelper;
class CreateStudyButton extends QMButton {
	public function __construct(){
		parent::__construct("Create a Study", null, QMColor::HEX_GOOGLE_BLUE, IonIcon::study);
		$this->setUrl(IonicHelper::getStudyCreationUrl());
	}
	/**
	 * @return CreateStudyButton
	 */
	public static function getCreateStudyButton(): CreateStudyButton{
		return new CreateStudyButton();
	}
}
