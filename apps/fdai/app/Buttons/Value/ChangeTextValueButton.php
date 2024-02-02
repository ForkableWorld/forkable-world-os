<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Value;
use App\Buttons\QMButton;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class ChangeTextValueButton extends QMButton {
	public $fontAwesome = FontAwesome::EDIT;
	public $ionIcon = IonIcon::edit;
	public function __construct(string $field, $currentValue, string $editUrl, string $tooltip){
		$title = QMStr::snakeToTitle($field);
		parent::__construct("Edit $title");
		$this->setTooltip($tooltip);
		$this->setParameters([$field => $currentValue]);
		$this->setTooltip($tooltip);
		// javascript:void() Prevents jumping to the top of page
		$this->setUrl("javascript:void(0)");
		if(is_string($currentValue)){
			$currentValue = "'$currentValue'";
		}
		$this->onClick = "qm.popup.putText('$editUrl', '$field', $currentValue);";
	}
}
