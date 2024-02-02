<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Charts\QMHighcharts;
use App\Charts\QMChart;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMDB;
use App\Storage\DB\QMQB;
use App\Storage\DB\ReadonlyDB;
use App\Storage\QueryBuilderHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
class DBHighstock extends BaseHighstock {
	protected $table;
	/**
	 * DBHighstock constructor.
	 * @param array|null $queryParams
	 * @param QMChart|null $QMChart
	 */
	public function __construct(array $queryParams = null, QMChart $QMChart = null){
		if(!$this->qmChart){$this->qmChart = $QMChart;}
		if($queryParams){
			$this->setQueryParams($queryParams);
		}
		parent::__construct($QMChart);
	}
	public function inlineNoHeading(): string{
		$data = $this->series[0]->data;
		if(!$data){
			$where = $this->getHumanizedWhereString();
			return "<h3>No data $where for chart</h3>";
		}
		return parent::inlineNoHeading();
	}
	/**
	 * @return array
	 */
	public function getRawData(): array{
		return $this->rawData;
	}
	/** @noinspection PhpUnused */
	public function getWhereClausesHtml(): string{
		$wheres = $this->getQueryParams();
		$items = "";
		if(!$wheres){
			return "";
		}
		foreach($wheres as $key => $value){
			$items = QueryBuilderHelper::whereParamsToHumanString($key, QMRequest::getTable(), $value);
		}
		return "
            <p class=\"card-category\">
                <h6>WHERE:</h6>
                <ul>
                    $items
                </ul>
            </p>
        ";
	}
	public function getQueryParams(): array{
		if(empty($this->queryParams) && $this->arguments){
			$this->queryParams = $this->arguments;
		}
		if(!is_array($this->queryParams)){
			le("queryParams should be an array but is " . \App\Logging\QMLog::print_r($this->queryParams, true));
		}
		return $this->queryParams;
	}
	protected function getHumanizedWhereString(): string{
		return QueryBuilderHelper::getHumanizedWhereClause($this->getQueryParams(), $this->getTable());
	}
	/**
	 * @return array
	 */
	public function getWhereClauseStrings(): array{
		if(!$this->whereClauseStrings){
			$this->whereClauseStrings =
				QueryBuilderHelper::addWhereClausesStringsFromRequest($this->getTable(), $this->getQueryParams());
		}
		return $this->whereClauseStrings;
	}
	/**
	 * @param array $queryParams
	 */
	public function setQueryParams(array $queryParams): void{
		$this->queryParams = $queryParams;
	}
	public function getSubtitleAttribute(): string{
		$subTitle = null;
		if($this->subtitle){
			$subTitle = $this->subtitle->text;
		}
		if(!$subTitle){
			$subTitle =  $this->getTableTitle() . " " . $this->getHumanizedWhereClause();
			$this->setSubtitle($subTitle);
		}
		return $subTitle;
	}
	/**
	 * @return string
	 */
	public function getHumanizedWhereClause(): string{
		$where = QMDB::paramsToHumanizedWhereClauseString($this->getQueryParams(), $this->getTable());
		return $where;
	}
	/** @noinspection PhpUnused */
	public function getLastChangeHtml(): string{
		$data = array_reverse($this->rawData);
		if(!isset($data[2])){
			return "<span class=\"text-danger\"><i class=\"fa fa-question\"></i> &nbsp; Not enough data to determine change! </span>";
		}
		$time = $data[1]->date;
		$time = TimeHelper::timeSinceHumanString($time);
		$second = $data[1]->value;
		$third = $data[2]->value;
		$changeCount = $second - $third;
		$change = round($changeCount / $third * 100);
		if($change > 0){
			$color = "success";
			$arrow = "up";
			$increaseDecrease = "increase";
		} else{
			$color = "danger";
			$arrow = "down";
			$increaseDecrease = "decrease";
		}
		return "<span class=\"text-$color\"><i class=\"fa fa-long-arrow-$arrow\"></i> $change% </span> $increaseDecrease $time";
	}
	public function qb(): QMQB{
		$qb = ReadonlyDB::getBuilderByTable($this->getTable());
		QueryBuilderHelper::addParams($qb, $this->getQueryParams());
		return $qb;
	}
	/**
	 * @return string
	 */
	public function getTable(): string{
		if(!$this->table){
			le('!$this->table');
		}
		return $this->table;
	}
	/**
	 * @return string
	 */
	public function getTableTitle(): string{
		return QMStr::tableToTitle($this->getTable());
	}
}
