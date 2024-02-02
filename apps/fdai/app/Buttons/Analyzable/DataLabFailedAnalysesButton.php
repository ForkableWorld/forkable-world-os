<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Analyzable;
use App\Buttons\DataLabIndex\DataLabSelectQueryButton;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
class DataLabFailedAnalysesButton extends DataLabSelectQueryButton {
	public $fontAwesome = FontAwesome::BUG_SOLID;
	public $ionIcon = IonIcon::bug;
	public $color = QMColor::HEX_RED;
	public $image = ImageUrls::ESSENTIAL_COLLECTION_ERROR;
	public function __construct($tableOrModel, array $params = []){
		$params['internal_error_message'] = "not null";
		$params['sort'] = "-analysis_started_at";
		parent::__construct($tableOrModel, $params);
		$m = $this->getModel();
		$this->fieldForChartXAxis = "analysis_started_at";
		$this->setTextAndTitle("Failed Analyses");
		$this->setTooltip("Failed " . QMStr::snakeToTitle($m->getTable()) . " Analyses");
	}
}
