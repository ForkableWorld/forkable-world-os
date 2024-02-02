<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Exceptions\InvalidVariableValueException;
use App\Models\Unit;
use App\Models\Variable;
use App\Slim\Model\QMUnit;
use App\Traits\HasModel\HasVariable;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariable;
class DailyAnonymousMeasurement extends AnonymousMeasurement {
	use HasVariable;
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
	 * @var array
	 */
	private $groupedMeasurements;
	/**
	 * DailyAnonymousMeasurement constructor.
	 * @param string $date
	 * @param array $measurements
	 * @param QMCommonVariable|QMVariable $v
	 * @param QMUnit $unit
	 * @throws InvalidVariableValueException
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(string $date, array $measurements, QMCommonVariable $v, QMUnit $unit){
		$values = [];
		foreach($measurements as $m){
			$values[] = $m->value;
		}
		$this->date = $date;
		$this->groupedMeasurements = $measurements;
		$this->startTime = strtotime($date);
		$this->unit = $unit;
		$this->value = $v->aggregateDailyValues($values);
		$this->variableId = $v->getVariableIdAttribute();
	}
	/**
	 * @param QMMeasurement[]|AnonymousMeasurement[] $all
	 * @param QMVariable|Variable $v
	 * @return DailyMeasurement[]
	 */
	public static function aggregateDaily(array $all, $v): array{
		$indexedByDate = [];
		foreach($all as $m){
			$indexedByDate[$m->getDate()][] = $m;
		}
		$unit = $v->getCommonUnit();
		$daily = [];
		foreach($indexedByDate as $date => $forDate){
			try {
				$dm = new static($date, $forDate, $v, $unit);
			} catch (InvalidVariableValueException $e) {
				$v->logError(__METHOD__.": ".$e->getMessage());
				continue;
			}
			$daily[$date] = $dm;
		}
		ksort($daily);
		return $daily;
	}
	public function getStartAt(bool $stripTrailingZeros = false): string{
		return $this->date . " 00:00:00";
	}
	public function getValueInUserUnit(): float{
		return $this->value;
	}
}
