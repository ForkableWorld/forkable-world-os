<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Exceptions\InvalidVariableValueException;
use App\Models\Measurement;
use App\Models\Unit;
use App\Slim\Model\QMUnit;
use App\Variables\QMUserVariable;
class DailyMeasurement extends QMMeasurement {
	/**
	 * @var string
	 */
	public $date;
	/**
	 * @var Unit
	 */
	protected $unit;
	public $duration = 86400;
	/**
	 * @param string $date
	 * @param array $measurements
	 * @param \App\Variables\QMUserVariable $uv
	 * @param \App\Slim\Model\QMUnit $unit
	 * @throws \App\Exceptions\InvalidVariableValueException
	 */
	public function __construct(string $date, array $measurements, QMUserVariable $uv, QMUnit $unit){
		$values = [];
		foreach($measurements as $m){
			$values[] = $m->value;
			foreach($m as $key => $value){
				if($value !== null){
					$this->$key = $value;
				}
			}
		}
		$this->date = $date;
		$this->startAt = $date . " 00:00:00";
		$this->groupedMeasurements = $measurements;
		$this->startTime = strtotime($date);
		$this->unit = $unit;
		$this->unitId = $unit->getId();
		$this->unitAbbreviatedName = $unit->getAbbreviatedName();
		$this->userId = $uv->getUserId();
		$this->userVariable = $uv;
		$this->userVariableId = $uv->id;
		$this->value = $uv->aggregateDailyValues($values);
		$this->variableId = $uv->getVariableIdAttribute();
		$this->variableName = $uv->variableName;
	}
	/**
	 * @param QMMeasurement[]|Measurement[] $all
	 * @param QMUserVariable $userVariable
	 * @return DailyMeasurement[]
	 */
	public static function aggregateDaily(array $all, QMUserVariable $userVariable): array{
		$byDate = $groups = [];
		foreach($all as $m){
			$groups[$m->getDate()][] = $m;
		}
		$unit = $userVariable->getCommonUnit();
		foreach($groups as $date => $forDate){
			try {
				$dm = new DailyMeasurement($date, $forDate, $userVariable, $unit);
				$byDate[$date] = $dm;
			} catch (InvalidVariableValueException $e) {
				$userVariable->addInvalidMeasurement($m, $e->getMessage());
			}
		}
		ksort($byDate);
		return $byDate;
	}
	public function getStartAt(bool $stripTrailingZeros = false): string{
		return $this->date . " 00:00:00";
	}
	public function getQMUserVariable(): QMUserVariable{
		if($this->userVariable){
			return $this->userVariable;
		}
		return $this->userVariable = QMUserVariable::find($this->getUserVariableId());
	}
}
