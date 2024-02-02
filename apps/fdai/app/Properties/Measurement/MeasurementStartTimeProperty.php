<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\BaseModel;
use App\Models\Measurement;
use App\Storage\QueryBuilderHelper;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseStartTimeProperty;
use App\Utils\Stats;
use App\Types\TimeHelper;
use App\Slim\Model\DBModel;
use App\Slim\Model\Measurement\AnonymousMeasurement;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\Measurement\RawQMMeasurement;
use App\Variables\QMCommonVariable;
use App\Slim\View\Request\QMRequest;
use App\PhpUnitJobs\Cleanup\MeasurementCleanUpJobTest;
use Illuminate\Database\Query\Builder;
class MeasurementStartTimeProperty extends BaseStartTimeProperty
{
    use MeasurementProperty;
    const PARAM_END_TIME = 'end_time';
    public $isUnixTime = true;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public function getTwinTimeAttribute(): string{return Measurement::FIELD_START_AT;}
    /**
     * @param BaseModel|array|object|DBModel|int|string $data
     * @return false|int|mixed|string|null
     */
    public static function pluck($data){
        if(is_int($data)){return $data;}
        if(is_string($data)){return time_or_exception($data);}
        $val = parent::pluck($data);
        return $val;
    }
	/**
	 * @param $data
	 * @return int
	 */
	public static function pluckRounded($data): int {
        $time = static::pluck($data);
        $v = MeasurementVariableIdProperty::findRelated($data);
        return $v->roundStartTime($time);
    }
    /**
     * @param BaseModel|array|object|DBModel|string|null $data
     * @return int
     */
    public static function getDefault($data = null): ?int{
        $at = MeasurementStartAtProperty::pluck($data);
        if($at){return strtotime($at);}
        return null;
    }
    public static function fixTooFrequentMeasurements(){
        $idsNumber = MeasurementCleanUpJobTest::getVariableIdsWithMostMeasurements(10);
        foreach ($idsNumber as $item) {
            $v = QMCommonVariable::find($item->variable_id);
            $v->logInfo("Measurements before: $item->measurements");
            $v->roundStartTimeAndDeleteExtraMeasurements();
        }
    }
    /**
     * @param int|string $timeAt
     * @param int $minimumSeconds
     * @return string
     */
    public static function roundToMinuteString($timeAt, int $minimumSeconds): string{
        $timeAt = TimeHelper::universalConversionToUnixTimestamp($timeAt);
        if($minimumSeconds === 1){
            return TimeHelper::getYmdHourMinuteString($timeAt);
        }
        $roundedSeconds = Stats::roundToNearestMultipleOf($timeAt, $minimumSeconds);
        return TimeHelper::getYmdHourMinuteString($roundedSeconds);
    }
    /**
     * @param int|string $timeAt
     * @param int $minimumSeconds
     * @return string
     */
    public static function round($timeAt, int $minimumSeconds): string{
        $timeAt = TimeHelper::universalConversionToUnixTimestamp($timeAt);
        if($minimumSeconds === 1){
            return db_date($timeAt);
        }
        $rounded = Stats::roundToNearestMultipleOf($timeAt, $minimumSeconds);
        return db_date($rounded);
    }
    /**
     * @param QMMeasurement[] $measurements
     * @param string|null $type
     * @param bool $allowDuplicates
     */
    public static function verifyChronologicalOrder(array $measurements,
                                                    string $type = null,
                                                    bool $allowDuplicates = false){
        if(count($measurements) < 2){return;}
        $last = QMMeasurement::last($measurements);
        $first = QMMeasurement::getFirst($measurements);
        $laterTime = $last->startTime;
        $earlierTime = $first->startTime;
		if($earlierTime > $laterTime){le( "$type Measurements must be in ascending chronological order!");}
        if(!$allowDuplicates && $earlierTime === $laterTime){
            $name0 = $first->getTagVariableNameOrVariableName();
            $name1 = $last->getTagVariableNameOrVariableName();
		    if($name0 === $name1){
		        le("Duplicate $name0 $type Measurements with time $laterTime!");
		    }
        }
    }
    public function getLatestUnixTime(): int{
        return self::generateLatestUnixTime();
    }
    /**
     * @throws \App\Exceptions\InvalidAttributeException
     */
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        parent::validate();
        if(!$this->exists()){
            $this->validateFrequency();
        }
        $m = $this->getMeasurement();
        $at = $m->getRawAttribute(Measurement::FIELD_START_AT);
        $this->assertDateEquals($at, Measurement::FIELD_START_AT);
    }
    /**
     * @throws \App\Exceptions\InvalidAttributeException
     */
    public function validateFrequency(): void{
        return; // TODO: Fix tests
        $variable = $this->getVariable();
        $measurement = $this->getMeasurement();
        $minSecs = $variable->minimum_allowed_seconds_between_measurements;
        $startTime = $this->getDBValue();
        $startAt = $measurement->start_at;
        $max = $startTime - $minSecs / 2;
        $min = $startTime + $minSecs / 2;
        $m = Measurement::whereUserVariableId($measurement->user_variable_id)
                ->where(Measurement::FIELD_START_TIME, ">", $max)
                ->where(Measurement::FIELD_START_TIME, "<", $min)
                ->first();
        if($m){
            $diff = TimeHelper::convertSecondsToHumanString($startTime - $m->start_time);
            $minDiff = TimeHelper::convertSecondsToHumanString($minSecs);
            $this->throwException("Cannot save measurement at $startAt because we already have a measurement in the same $minDiff period at ".
                $m->start_at." ($diff from existing)");
        }
    }
    public function cannotBeChangedToNull(): bool{
        return true;
    }
    /**
     * @param QMMeasurement[]|RawQMMeasurement[] $measurements
     * @param string|null $type
     * @param bool $allowDuplicates
     * @return QMMeasurement[]|RawQMMeasurement[]|AnonymousMeasurement[]|\App\Slim\Model\Measurement\DailyMeasurement[]
     */
    public static function sortMeasurementsChronologically(array $measurements,
                                                           string $type = null,
                                                           bool $allowDuplicates = false): array{
        usort($measurements, [
            __CLASS__,
            "sort_objects_by_start_time"
        ]);
        if(count($measurements) > 1){
            MeasurementStartTimeProperty::verifyChronologicalOrder($measurements, $type, $allowDuplicates);
        }
        return $measurements;
    }
    /**
     * @param $a
     * @param $b
     * @return int
     */
    public static function sort_objects_by_start_time($a, $b): int{
        /** @noinspection TypeUnsafeComparisonInspection */
        if($a->startTime == $b->startTime){
            return 0;
        }
        return ($a->startTime < $b->startTime) ? -1 : 1;
    }
    /**
     * @param int|string $time
     * @return int
     */
    public function toDBValue($time): ?int {
        if(!$time){return null;}
        $v = $this->getVariable();
        $rounded = $v->roundStartTime($time);
        return $rounded;
    }
	/**
	 * @param $data
	 * @return int|mixed|null
	 */
	public static function pluckOrDefault($data){
        if(is_int($data)){
            return $data;
        }
        return parent::pluckOrDefault($data);
    }
    /**
     * @param bool $throwException
     * @return int|string|null
     */
    public static function fromRequest(bool $throwException = false){
        $val = parent::fromRequest($throwException);
        if(is_string($val) && stripos($val, '(') === false){
            return time_or_exception($val);
        }
        return $val;
    }
	/**
	 * @param $data
	 * @param bool $fallback
	 * @return int|null
	 */
	public function pluckAndSetDBValue($data, bool $fallback = false): ?int{
        $val = static::pluck($data);
        if($val !== null){
            return $this->processAndSetDBValue($val);
        }
        return null;
    }
    protected static function applyRequestFiltersToQuery(Builder $qb): void{
        if($endtime = QMRequest::getParam(self::PARAM_END_TIME)){
            $endtime = TimeHelper::universalConversionToUnixTimestamp($endtime);
            $qb->where(static::getTable().'.'.static::NAME, "<=", $endtime);
            if($startTime = static::fromRequest()){
                $qb->where(static::getTable().'.'.static::NAME, ">=", $startTime);
            }
        } else {
            $val = static::fromRequest();
            if($val === null){return;}
            QueryBuilderHelper::applyFilters($qb, [static::NAME => $val]);
        }
    }
	public function showOnCreate(): bool{return false;}
}
