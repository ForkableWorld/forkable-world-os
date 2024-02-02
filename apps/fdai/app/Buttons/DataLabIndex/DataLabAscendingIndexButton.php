<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLabIndex;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\IonIcon;
use App\UI\QMColor;
class DataLabAscendingIndexButton extends DataLabSelectQueryButton {
	public $fontAwesome = FontAwesome::ARROW_CIRCLE_UP_SOLID;
	public $ionIcon = IonIcon::androidArrowDown;
	public $color = QMColor::HEX_BLUE;
	public function __construct($tableOrModel, string $field){
		parent::__construct($tableOrModel, ['sort' => "$field"]);
		$m = $this->getModel();
		$this->fieldForChartXAxis = $field;
		$title = "Least " . QMStr::snakeToTitle($field);
		$this->setTextAndTitle($title);
		$this->setTooltip("See " . $m->getPluralizedClassName() . " with $title");
		$this->setImage(IonIcon::getIonIconPngUrl(IonIcon::androidArrowUp));
	}
}
