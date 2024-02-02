<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\DataLabIndex;
use App\Models\BaseModel;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
class DataLabTrashButton extends DataLabSelectQueryButton {
	public $fontAwesome = FontAwesome::TRASH_SOLID;
	public $ionIcon = IonIcon::trashA;
	public $image = ImageUrls::BASIC_FLAT_ICONS_TRASH;
	public $color = QMColor::HEX_RED;
	/**
	 * @param string|BaseModel $tableOrModel
	 * @param array $params
	 */
	public function __construct($tableOrModel, array $params = []){
		$params[BaseModel::FIELD_DELETED_AT] = "NOT NULL";
		parent::__construct($tableOrModel, $params);
		$m = $this->getModel();
		$this->setTextAndTitle("Recycle Bin");
		$this->setTooltip("Deleted " . QMStr::snakeToTitle($m->getTable()));
		$this->fieldForChartXAxis = BaseModel::FIELD_DELETED_AT;
	}
}
