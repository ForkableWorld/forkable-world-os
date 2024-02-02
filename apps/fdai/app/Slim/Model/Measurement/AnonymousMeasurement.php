<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Measurement;
use App\Computers\ThisComputer;
use App\Exceptions\InsufficientMemoryException;
use App\Models\Measurement;
use App\Models\Variable;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Storage\DB\Writable;
use App\Traits\IonicTrait;
use App\Traits\ModelTraits\MeasurementTrait;
use App\Types\TimeHelper;
use App\Utils\QMProfile;
use App\Utils\Stats;
use App\Variables\QMCommonVariable;
use App\Variables\QMVariable;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use DB;

/**
 * @mixin Measurement
 */
class AnonymousMeasurement extends DBModel {
	use IonicTrait, MeasurementTrait;
	const AVG   = 'AVG';
	const SUM   = 'SUM';
	const Y_M_D = '%Y-%m-%d';
	protected $carbon;
	protected $hourNumber;
	protected $monthNumber;
	protected $taggedVariable;
	protected $taggedVariableMeasurement;
	protected $weekdayNumber;
	protected $year;
	protected $date;
	public $additionalMetaData;
	public $startTime;
	public $startTimeString;
	public $startAt;
	public $value;
	public $variableId;
	public const BYTES_PER_INSTANCE         = 523;
	public const FIELD_CLIENT_ID            = 'client_id';
	public const FIELD_CONNECTOR_ID         = 'connector_id';
	public const FIELD_CONNECTION_ID        = 'connection_id';
	public const FIELD_CREATED_AT           = 'created_at';
	public const FIELD_DELETED_AT           = 'deleted_at';
	public const FIELD_DURATION             = 'duration';
	public const FIELD_ERROR                = 'error';
	public const FIELD_ID                   = 'id';
	public const FIELD_LATITUDE             = 'latitude';
	public const FIELD_LOCATION             = 'location';
	public const FIELD_LONGITUDE            = 'longitude';
	public const FIELD_NOTE                 = 'note';
	public const FIELD_ORIGINAL_UNIT_ID     = 'original_unit_id';
	public const FIELD_ORIGINAL_VALUE       = 'original_value';
	public const FIELD_SOURCE_NAME          = 'source_name';
	public const FIELD_START_AT             = 'start_at';
	public const FIELD_START_TIME           = 'start_time';
	public const FIELD_UNIT_ID              = 'unit_id';
	public const FIELD_UPDATED_AT           = 'updated_at';
	public const FIELD_USER_ID              = 'user_id';
	public const FIELD_USER_VARIABLE_ID     = 'user_variable_id';
	public const FIELD_VALUE                = 'value';
	public const FIELD_VARIABLE_CATEGORY_ID = 'variable_category_id';
	public const FIELD_VARIABLE_ID          = 'variable_id';
	public const LARAVEL_CLASS              = Measurement::class;
	public const TABLE                      = 'measurements';
	/**
	 * AnonymousMeasurement constructor.
	 * @param AnonymousMeasurement|object $row
	 */
	public function __construct($row = null){
		if(!$row){
			return;
		}
		foreach($row as $key => $value){
			if(property_exists($this, $key)){
				$this->$key = $value;
			}
		}
		// Trying to reduce memory usage when lots of measurements
		//if(!isset($measurementRow->startTimeString)){$this->startTimeString = date('Y-m-d H:i:s', $measurementRow->startTime);}
	}
	/**
	 * @param int $variableId
	 * @param int|null $userId
	 * @return AnonymousMeasurement[]
	 * @throws InsufficientMemoryException
	 */
	public static function getAverageByHour(int $variableId, int $userId = null): array{
		return self::getByVariableId($variableId, $userId, '%Y-%m-%d %H', self::AVG);
	}
	/**
	 * @param int $variableId
	 * @param int|null $userId
	 * @param string|null $dateGrouping
	 * @param string $aggregationMethod
	 * @return AnonymousMeasurement[]
	 */
	public static function getByVariableId(int $variableId, ?int $userId, ?string $dateGrouping,
		string $aggregationMethod): array{
		if(empty($dateGrouping)){
			return self::getUnGrouped($variableId, $userId);
		}
		$userWhereClause = '';
		if($userId){
			$userWhereClause = " AND user_id=$userId ";
		}
        if(!Writable::isMySQL()){
            $rows = DB::query()->from('measurements')->where('variable_id', $variableId)
                ->select('value', 'start_at')
                ->get();
            $valuesByDate = [];
            foreach ($rows as $row){
                $valuesByDate[TimeHelper::YYYYmmddd($row->start_at)][] = $row->value;
            }
            $rows = $aggregated = [];
            foreach ($valuesByDate as $date => $values){
                if($aggregationMethod === self::SUM) {
                    $aggregated[$date] = Stats::sum($values);
                } else {
                    $aggregated[$date] = Stats::average($values);
                }
            }
            foreach ($aggregated as $date => $value){
                $rows[$date] = [
                    'startTimeString' => db_date($date),
                    'value' => $value,
                    'startTime' => TimeHelper::universalConversionToUnixTimestamp($date)
                ];
            }
        } else {
            $avgStart = 'FROM_UNIXTIME(AVG(start_time)) as startTimeString';
            $dateGroupingWhereClause = " GROUP BY date_format( FROM_UNIXTIME(start_time), '$dateGrouping') ";
            $rows = Writable::selectStatic("
                SELECT
                  $aggregationMethod(value) as value,
                  ROUND(AVG(start_time)) as startTime,
                  $avgStart
                FROM measurements
                WHERE deleted_at IS NULL
                  AND variable_id=$variableId
                      $userWhereClause
                $dateGroupingWhereClause "// ORDER BY startTime ASC // Really SLOW
            );
        }


		$number = count($rows);
		if($profile = false){
			ThisComputer::removeMemoryLimit();
			ThisComputer::logMemoryUsage($number . " rows");
			QMProfile::profileIfEnvSet(false, false, __METHOD__);
		}
		$measurements = [];
		ThisComputer::exceptionIfInsufficientMemoryForArray(count($rows), self::BYTES_PER_INSTANCE);
		foreach($rows as $row){
			$m = new AnonymousMeasurement($row);
			$m->variableId = $variableId;
			$measurements[$m->getStartAt()] = $m;
		}
		ksort($measurements); // Must be sorted here. We don't sort in database because it's slow.
		/** @noinspection PhpConditionAlreadyCheckedInspection */
		if($profile){QMProfile::endProfile();}
		return $measurements;
	}
	/**
	 * @param int $variableId
	 * @param int|null $userId
	 * @return AnonymousMeasurement[]
	 */
	private static function getUnGrouped(int $variableId, int $userId = null): array{
		$userWhereClause = '';
		if($userId){
			$userWhereClause = " AND user_id=$userId ";
		}
		$query = "
                SELECT
                  value,
                  start_time as startTime " . //, FROM_UNIXTIME(start_time) as startTimeString
			"FROM measurements
                WHERE deleted_at IS NULL AND
                    variable_id=$variableId $userWhereClause " .
			// GROUP BY reduces memory usage for variables like Barometric Pressure
			"GROUP BY value, start_time "//."ORDER BY startTime ASC " // Order By greatly slows query
		;
		$rows = Writable::selectStatic($query);
		ThisComputer::outputMemoryUsageIfEnabledOrDebug("got " . count($rows) .
			" measurements for variable $variableId");
		$measurements = [];
		ThisComputer::exceptionIfInsufficientMemoryForArray(count($rows), self::BYTES_PER_INSTANCE);
		foreach($rows as $row){
			$m = new static($row);
			$measurements[$m->getStartAt()] = $m;
		}
		ksort($measurements); // Must be sorted here. We don't sort in database because it's slow.
		return $measurements;
	}
	/**
	 * @param int $variableId
	 * @param int|null $userId
	 * @return AnonymousMeasurement[]
	 */
	public static function getAveragedByDate(int $variableId, int $userId = null): array{
		$measurements = self::getByVariableId($variableId, $userId, self::Y_M_D,
			self::AVG);
		$byDate = [];
		foreach($measurements as $m){
			$byDate[$m->getDate()] = $m;
		}
		return $byDate;
	}
	/**
	 * @param int $variableId
	 * @param int|null $userId
	 * @return static[]
	 */
	public static function getSumByDate(int $variableId, int $userId = null): array{
		$measurements = self::getByVariableId($variableId, $userId, self::Y_M_D,
			self::SUM);
		$byDate = [];
		foreach($measurements as $m){
			$byDate[$m->getDate()] = $m;
		}
		return $byDate;
	}
	/**
	 * @return string
	 */
	public function getStartAt(): string{
		$str = $this->startTimeString ?? $this->startAt;
		if($str){
			return $str;
		}
		$startTime = $this->getOrSetStartTime();
		return $this->setStartAt(db_date($startTime));
	}
	/**
	 * @param string $startAt
	 * @return string
	 */
	public function setStartAt(string $startAt): string{
		return $this->startAt = $this->startTimeString = $startAt;
	}
	/**
	 * @return int
	 */
	public function getHourNumber(): int{
		return $this->hourNumber ?: $this->setHourNumber();
	}
	/**
	 * @return int
	 */
	protected function setHourNumber(): int{
		return $this->hourNumber = $this->getCarbon()->hour;
	}
	/**
	 * @return int
	 */
	public function getWeekdayNumber(): int{
		return $this->weekdayNumber ?: $this->setWeekdayNumber();
	}
	/**
	 * @return int
	 */
	protected function setWeekdayNumber(): int{
		return $this->weekdayNumber = $this->getCarbon()->dayOfWeek;
	}
	/**
	 * @return int
	 */
	public function getMonthNumber(): int{
		return $this->monthNumber ?: $this->setMonthNumber();
	}
	/**
	 * @return int
	 */
	protected function setMonthNumber(): int{
		return $this->monthNumber = $this->getCarbon()->month;
	}
	/**
	 * @return int
	 */
	public function getYear(): int{
		return $this->year ?: $this->setYear();
	}
	/**
	 * @return int
	 */
	protected function setYear(): int{
		return $this->year = $this->getCarbon()->year;
	}
	/**
	 * @return float
	 */
	public function getValue(): float{
		return $this->value;
	}
	/**
	 * @return QMVariable
	 */
	public function getTaggedVariable(): ?QMVariable{
		return $this->taggedVariable;
	}
	/**
	 * @return int
	 */
	public function getVariableIdAttribute(): ?int{
		return $this->variableId;
	}
	/**
	 * @return Carbon
	 */
	private function getCarbon(): CarbonInterface{
		return $this->carbon ?: $this->setCarbon();
	}
	/**
	 * @return Carbon
	 */
	private function setCarbon(): CarbonInterface{
		return $this->carbon = TimeHelper::toCarbon($this->startTime);
	}
	/**
	 * @param QMVariable $taggedVariable
	 */
	public function setTaggedVariable(QMVariable $taggedVariable): void{
		$this->taggedVariable = $taggedVariable;
	}
	/**
	 * @return array
	 */
	public function toSpreadsheetRow(): array{
		return [
			'Value' => $this->getValue(),
			'Measurement Event Time' => $this->getStartAt(),
		];
	}
	/**
	 * @param AnonymousMeasurement $taggedVariableMeasurement
	 */
	public function setTaggedVariableMeasurement(AnonymousMeasurement $taggedVariableMeasurement): void{
		$this->taggedVariableMeasurement = $taggedVariableMeasurement;
	}
	/**
	 * @return QMMeasurement
	 */
	public function getTaggedVariableMeasurement(): ?AnonymousMeasurement{
		return $this->taggedVariableMeasurement;
	}
	/**
	 * @return QMVariable
	 */
	public function getQMVariable(): QMVariable{
		return QMCommonVariable::find($this->getVariableIdAttribute());
	}
	/**
	 * @return string
	 */
	public function getVariableName(): ?string{
		if(!isset($this->variableName)){
			$v = $this->getQMVariable();
			return $this->variableName = $v->getVariableName();
		}
		return $this->variableName;
	}
	/**
	 * @return string
	 */
	public function getTagVariableNameOrVariableName(): string{
		$tag = $this->getTaggedVariableMeasurement();
		if($tag){
			return $tag->getVariableName();
		}
		return $this->getVariableName();
	}
	/**
	 * @param array $requestParams
	 * @return array
	 */
	public static function getIndexedByVariableName(array $requestParams = []): array{
		/** @var QMMeasurement[] $measurements */
		$measurements = static::get($requestParams);
		$indexed = [];
		foreach($measurements as $m){
			$indexed[$m->getVariableName()][$m->getStartAt()] = $m;
		}
		return $indexed;
	}
	/**
	 * @return QMCommonVariable
	 */
	public function getCommonVariable(): QMCommonVariable{
		return QMCommonVariable::findByNameIdOrSynonym($this->getVariableIdAttribute());
	}
	public function getValueInUserUnit(): float{
		return $this->value;
	}
	public function getUserUnit(): QMUnit{
		return QMUnit::find($this->getUserUnitId());
	}
	public function getUserUnitId(): int{
		return $this->getQMVariable()->getUnitIdAttribute();
	}
	/**
	 * @return int
	 */
	public function getOrSetStartTime(): int{
		if($time = $this->startTime){
			return $time;
		}
		if($at = $this->startAt){
			return $this->startTime = strtotime($at);
		}
		le("no startAt time!");
		throw new \LogicException();
	}
	public function getMillis(): int{
		return 1000 * $this->getOrSetStartTime();
	}
	/**
	 * @param bool $useAbbreviatedName
	 * @param int $sigFigs
	 * @return string
	 */
	public function getValueUnitString(bool $useAbbreviatedName = false, int $sigFigs = 3): string{
		$v = Stats::roundByNumberOfSignificantDigits($this->value, $sigFigs);
		if(!$this->unitId && $this->originalUnit){
			return $this->getOriginalUnit()->getValueAndUnitString($v, $useAbbreviatedName);
		}
		$u = $this->getQMUnit();
		if(!$u){
			le("No unit!");
		}
		return $u->getValueAndUnitString($v, $useAbbreviatedName);
	}
	/**
	 * @return float
	 */
	public function getDBValue(): float{
		return $this->getValueInCommonUnit();
	}
	public function getVariable(): Variable{
		return Variable::findInMemoryOrDB($this->getVariableIdAttribute());
	}
	public function getSubtitleAttribute(): string{
		return $this->getValueUnitTime();
	}
	public function getMeasurement(): Measurement{
		return $this->l();
	}
	/**
	 * @return Measurement
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function l(){
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return parent::l();
	}
	/**
	 * @return string
	 */
	public function getDate(): string{
		if($this->date){
			return $this->date;
		}
		return $this->date = TimeHelper::YYYYmmddd($this->startTime);
	}
	public function getVariableCategoryId(): int{
		return $this->getVariable()->getVariableCategoryId();
	}
	public function getUnitIdAttribute(): ?int{
		return $this->getCommonVariable()->getCommonUnitId();
	}
	public function getTitleAttribute(): string{
		return $this->getValueUnitString()." ".$this->getVariableName();
	}
	/**
	 * @return string
	 */
	public function getStartAtAttribute(): string{
		if(!$this->startAt){
			$this->startAt = db_date($this->startTime);
		}
		return $this->startAt;
	}
	public function getUserId(): ?int{
		return null;
	}
}
