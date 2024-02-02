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
class DeleteValueButton extends QMButton {
	public $fontAwesome = FontAwesome::DELETE;
	public $ionIcon = IonIcon::DELETE;
	public function __construct(string $field, $currentValue, string $editUrl, string $name){
		$title = QMStr::snakeToTitle($field);
		parent::__construct("Delete $title");
		$this->setTooltip("Change $title from $currentValue to NULL");
		$this->setParameters([$field => null]);
		$this->setUrl("javascript:void(0)");
		if(is_string($currentValue)){
			$currentValue = "'$currentValue'";
		}
		$this->onClick = "qm.popup.confirmAndDeleteValue('$editUrl', '$field', $currentValue, '$name');";
	}
}
