<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Analyzable;
use App\Buttons\DataLabIndex\DataLabSelectQueryButton;
use App\Models\BaseModel;
use App\Types\QMStr;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
class DataLabNeverAnalyzedButton extends DataLabSelectQueryButton {
	public $image = ImageUrls::QUESTION_MARK;
	public $color = QMColor::HEX_BLUE;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public function __construct($tableOrModel, array $params = []){
		$params['analysis_ended_at'] = "NULL";
		parent::__construct($tableOrModel, $params);
		$m = $this->getModel();
		$this->fieldForChartXAxis = BaseModel::FIELD_DELETED_AT;
		$this->setTextAndTitle("Never Analyzed");
		$this->setTooltip(QMStr::tableToTitle($m->getTable()) . " Never Analyzed");
	}
	public static function whereTable(string $table, array $params = []): DataLabNeverAnalyzedButton{
		return new static($table, $params);
	}
}
