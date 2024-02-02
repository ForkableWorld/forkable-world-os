<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Models\BaseModel;
use App\Storage\DB\Writable;
use App\Types\QMStr;
use App\Types\TimeHelper;
class AggregatedColumnHighstockChart extends DBHighstock {
	public $field;
	public $tableCount;
	public function __construct(string $table, string $field, array $params = null, string $title = null,
		string $subTitle = null){
		parent::__construct($params);
		$this->table = $table;
		$this->field = $field;
		$fieldTitle = $this->getFieldTitle();
		$tableTitle = $this->getTableTitle();
		if($title === null){
			$title = "$tableTitle $fieldTitle per Day";
			$this->setTitle($title);
		} elseif(!empty($title)){
			$this->setTitle($title);
		}
		if($subTitle === null){
			$this->setSubTitle($this->getSubtitleAttribute());
		} elseif(!empty($subTitle)){
			$this->setSubTitle($subTitle);
		}
		$this->getWhereClauseStrings();
		$this->rawData = $results = Writable::getCountAggregatedByDay($table, $field, $params);
		$series = new HighstockSeries($title, $this, $tableTitle);
		$series->setColor($this->colors[0]);
		foreach($results as $result){
			$carbon = TimeHelper::toCarbon(strtotime($result->date));
			$year = $carbon->year;
			$month = $carbon->month - 1;
			$day = $carbon->day;
			$series->data[] = [new HighchartJsExpr("Date.UTC($year, $month, $day)"), $result->value];
		}
		$series->lineWidth = 4;
		$this->addSeriesWithLabels($series);
	}
	public function getSumOfValues(): int{
		$raw = $this->getRawData();
		$sum = 0;
		foreach($raw as $item){
			$sum += $item->value;
		}
		return $sum;
	}
	/**
	 * @param string $table
	 */
	public function setTable(string $table): void{
		$this->table = $table;
	}
	/**
	 * @return string
	 */
	public function getField(): string{
		return $this->field;
	}
	/**
	 * @return string
	 */
	public function getFieldTitle(): string{
		return QMStr::humanizeFieldName($this->getField());
	}
	/**
	 * @param string $field
	 */
	public function setField(string $field): void{
		$this->field = $field;
	}

	public function inlineNoHeading(): string{
		$data = $this->series[0]->data;
		if(!$data){
			$where = $this->getHumanizedWhereString();
			return "<h3>No $this->table $where for chart</h3>";
		}
		return parent::inlineNoHeading();
	}
	public function getModelIndexUrl(): string{
		return BaseModel::generateDataLabIndexUrl($this->getQueryParams(), $this->getTable());
	}
	public function getCardHtml(): string{
		$url = $this->getModelIndexUrl();
		$icon = $this->getIcon();
		$title = $this->getTitleAttribute();
		$description = $this->getSubtitleAttribute();
		$sum = $this->getSumOfValues();
		$lastChange = $this->getLastChangeHtml();
		$this->setCardBody("
            <h4 class=\"card-title\">
                <a href=\"$url\"  style=\"color: #3C4858; text-decoration: none;\">
                    <i class=\"$icon\"></i> $sum $title
                </a>
            </h4>
            <p class=\"card-category\">$lastChange</p>
            <p class=\"card-category\">$description</p>
        ");
		return parent::getCardHtml();
	}
}
