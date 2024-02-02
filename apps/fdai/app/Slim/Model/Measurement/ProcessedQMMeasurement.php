<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Slim\Model\QMUnit;
use App\Variables\QMVariableCategory;
class ProcessedQMMeasurement extends QMMeasurementExtended {
	public $startDate;
	public $unitName;
	public $variableCategoryName;
	/**
	 * ProcessedMeasurement constructor.
	 * @param QMMeasurement $raw
	 * @param $groupStartTime
	 */
	public function __construct($raw, $groupStartTime){
		if(!$raw){
			return;
		}
		foreach($raw as $key => $value){
			$this->$key = $value;
		}
		if(isset($raw->unitId)){
			$this->unitAbbreviatedName = QMUnit::getUnitById($raw->unitId)->abbreviatedName;
			$this->unitName = QMUnit::getUnitById($raw->unitId)->name;
		} elseif(isset($raw->unitAbbreviatedName)){
			$this->unitAbbreviatedName = $raw->getUnitAbbreviatedName();
			$this->unitId = $raw->getUnitIdAttribute();
			$this->unitName = $raw->getQMUnit()->name;
		}
		if(isset($raw->variableCategoryId)){
			$this->variableCategoryName = QMVariableCategory::find($raw->variableCategoryId)->name;
		}
		$this->startTimeEpoch = $this->startTime = $groupStartTime;
		$this->startTimeString = date('Y-m-d H:i:s', $groupStartTime);
		$this->setStartAt(db_date($groupStartTime));
	}
	public function setVariableCategory($cat): QMVariableCategory{
		$cat = parent::setVariableCategory($cat);
		$this->variableCategoryName = $cat->name;
		return $cat;
	}
	/**
	 * @param $a
	 * @param $b
	 * @return int
	 */
	public static function sort_objects_by_start_time($a, $b){
		if($a->startTime == $b->startTime){
			return 0;
		}
		return ($a->startTime < $b->startTime) ? -1 : 1;
	}
}
