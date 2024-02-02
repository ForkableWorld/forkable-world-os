<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits\PropertyTraits;
use App\Exceptions\InvalidAttributeException;
use App\Models\BaseModel;
use App\Types\TimeHelper;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Form;
use Illuminate\Database\Eloquent\Collection;
use App\Fields\DateTime;
use App\Fields\Field;
use App\Fields\Text;
trait IsTemporal {
	protected $carbon;
	/**
	 * @throws InvalidAttributeException
	 */
	public function assetGreaterThanYear2000(){
		$unix = $this->getUnixTime();
		if($unix < TimeHelper::YEAR_2000_UNIXTIME){
			$date = $this->getDBValue();
			$this->throwException("should not be earlier than the year 2000 but is $date");
		}
	}
	/**
	 * @throws InvalidAttributeException
	 */
	public function assetNotInTheFuture(){
		$value = $this->getDBValue();
		if($value > time()){
			$date = $this->getDateTime();
			$this->throwException("should not be in the future but is $date");
		}
	}
	public function validate(): void {
		$val = $this->getDBValue();
		if($val === null){
			$required = $this->isRequired();
			if(!$required){
				return;
			}
			$this->throwException("required but is null");
		}
		$this->validateTime();
	}
	/**
	 * @throws InvalidAttributeException
	 */
	public function validateTime(){
		if(!$this->shouldValidate()){
			return;
		}
		$value = $this->getDBValue();
		$required = $this->cannotBeChangedToNull();
		if($value === null){
			if(!$required){
				return;
			}
			$this->throwException("required but is null");
		}
		$this->assertNotTooLate();
		$this->assertNotTooEarly();
	}
	/**
	 * @throws InvalidAttributeException
	 */
	public function assertNotTooLate(){
		$unixTime = $this->getUnixTime();
		if(!$unixTime){
			le('!$unixTime');
		}
		$latest = $this->getLatestUnixTime();
		if(!$latest){
			le('!$latest');
		}
		if($unixTime > $latest){
			$latest = $this->getLatestUnixTime();
			$date = db_date($unixTime);
			$latestAt = db_date($latest);
			$this->throwException("
\t$date is after latest allowed:
\t$latestAt");
		}
	}
	/**
	 * @param string $message
	 * @throws InvalidAttributeException
	 */
	abstract public function throwException(string $message);
	/**
	 * @throws InvalidAttributeException
	 */
	public function assertNotTooEarly(){
		$unixTime = $this->getUnixTime();
		if(!$unixTime){
			le('!$unixTime');
		}
		$earliestUnixTime = $this->getEarliestUnixTime();
		if(!$earliestUnixTime){
			le('!$earliestUnixTime');
		}
		if($unixTime < $earliestUnixTime){
			$date = db_date($unixTime);
			$earliestAt = db_date($earliestUnixTime);
			$this->throwException("
\t$date is before earliest allowed:
\t$earliestAt");
		}
	}
	/**
	 * @return Carbon|CarbonInterface
	 */
	public function getCarbon(): Carbon{
		if($c = $this->carbon){
			return $c;
		}
		$val = $this->getDBValue();
		try {
			return $this->carbon = TimeHelper::toCarbon($val);
		} catch (\Throwable $e) {
			return $this->carbon = TimeHelper::toCarbon($val);
		}
	}
	public function getLatestUnixTime(): int{
		return time() + 86400;
	}
	public function getEarliestUnixTime(): int{
		return 1;
	}
	public function getLatestAt(): string{
		$time = $this->getLatestUnixTime();
		if(!$time){
			le('!$time');
		}
		return db_date($time);
	}
	public function getEarliestAt(): string{
		$time = $this->getEarliestUnixTime();
		if(!$time){
			le('!$time');
		}
		return db_date($time);
	}
	/**
	 * @param $earliest
	 * @param $latest
	 * @throws InvalidAttributeException
	 */
	public function assertEarliestBeforeLatest($earliest, $latest){
		if(!$earliest){
			return;
		}
		if(!$latest){
			return;
		}
		$earliestUnix = (is_int($earliest)) ? $earliest : strtotime($earliest);
		$latestUnix = (is_int($latest)) ? $latest : strtotime($latest);
		if($earliestUnix > $latestUnix){
			$earliestDate = db_date($earliestUnix);
			$latestDate = db_date($latestUnix);
			$this->throwException("Earliest ($earliestDate) should be earlier than latest ($latestDate)");
		}
	}
	/**
	 * @return void
     */
	public static function fixInvalidRecords()
    {   static::fixTooLate();
        static::fixTooEarly();
        //return array_merge(, static::fixTooEarly());
	}
	public function getInput(): string{
		$value = $this->getAccessorValue();
		$title = $this->getTitleAttribute();
		$name = $this->name;
		$label = Form::label($name, $title);
		return $label . Form::date($name, $value, [
				'class' => 'form-control',
				'id' => $name,
			]);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getDateTimeField($resolveCallback = null, string $name = null): Field{
		return (new DateTime($name ?? $this->getTitleAttribute(), $this->name,
			$resolveCallback ?? function($value, $resource, $attribute){
				return $value;
			}))->sortable(true);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 * TODO: What's this for?
	 */
	public function getSimpleDateTimeField($resolveCallback = null, string $name = null): Field{
		return $this->getDateTimeField($resolveCallback, $name)->format(TimeHelper::MOMENT_SIMPLE_DATE_TIME);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 */
	public function getDayOfWekTimeField($resolveCallback = null, string $name = null): Field{
		return $this->getDateTimeField($resolveCallback, $name)->format(TimeHelper::MOMENT_DAY_OF_WEEK_TIME);
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getFromNowField($resolveCallback = null, string $name = null): Field{
		return Text::make($name ?? $this->getTitleAttribute(), $this->name)->displayUsing(function($value, $resource,
			$attribute){
			return TimeHelper::toCarbon($value)->diffForHumans();
		})->exceptOnForms();
	}
	public static function generateEarliestAt(): string{
		return (new static())->getEarliestAt();
	}
	public static function generateEarliestUnixTime(): int{
		return (new static())->getEarliestUnixTime();
	}
	/**
	 * @return int|string
	 */
	public function getExample(){
		if($this->isUnixtime()){
			return $this->getExampleUnixTime();
		}
		return $this->getExampleDateTime();
	}
	public function getExampleUnixTime(): int{
		if($earliestUnixTime = $this->getEarliestUnixTime()){
			return $this->example = $earliestUnixTime + 1;
		}
		if($latestUnixTime = $this->getLatestUnixTime()){
			return $this->example = $latestUnixTime - 1;
		}
		return time() - 1;
	}
	public function getExampleDateTime(): string{
		return db_date($this->getExampleUnixTime());
	}
	public function getUnixTime(): ?int{
		$p = $this->getParentModel();
		$val = $p->getRawAttribute($this->name); // bypassAccessor bypasses mutation
		return time_or_null($val);
	}
	/**
	 * @return false|int|null|string
	 */
	public function getDBValue(){
		$p = $this->getParentModel();
		$val = $p->getRawAttribute($this->name); // bypassAccessor bypasses mutation
		if($this->isUnixtime()){
			return time_or_null($val);
		}
		return date_or_null($val);
	}
	public function getDateTime(): ?string{
		$p = $this->getParentModel();
		$val = $p->getRawAttribute($this->name); // bypassAccessor bypasses mutation
		return date_or_null($val);
	}
	/**
	 * @param $value
	 * @return float|int|null|string
	 */
	public function setOriginalValueAndConvertToDBValue($value){
		if($value === null){
			return null;
		}
		if($this->isUnixtime()){
			return TimeHelper::universalConversionToUnixTimestamp($value);
		}
		if(!$value){
			le('!$value');
		}
		return db_date($value);
	}
	/**
	 * @param $new
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function lessThanExisting($new): bool{
		/** @var IsTemporal $prop */
		$existing = $this->getUnixtime();
		$new = TimeHelper::universalConversionToUnixTimestamp($new);
		return $existing > $new;
	}
	/**
	 * @param $new
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function greaterThanExisting($new): bool{
		/** @var IsTemporal $prop */
		$existing = $this->getUnixtime();
		$new = TimeHelper::universalConversionToUnixTimestamp($new);
		return $existing < $new;
	}
	/**
	 * @param $expectedTimeAt
	 * @param string $expectedName
	 * @throws InvalidAttributeException
	 */
	protected function assertDateEquals($expectedTimeAt, string $expectedName){
		$actualTimeAt = $this->getDBValue();
		$res = TimeHelper::dateEquals($expectedTimeAt, $actualTimeAt);
		if(!$res){
			$actualDate = db_date($actualTimeAt);
			$at = db_date($expectedTimeAt);
			$this->throwException("should equal $expectedName value:\n\t$at but is:\n\t$actualDate");
		}
	}
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return DateTime
	 */
	public static function simpleDateTimeField($resolveCallback = null, string $name = null): DateTime{
		$p = new static();
		return $p->getSimpleDateTimeField($resolveCallback, $name);
	}
	/**
	 * @return false|string
	 */
	public function getDate(): string{
		$t = $this->getUnixTime();
		if(!$t){
			return "Unknown Date";
		}
		return date('Y-m-d', $t);
	}
}
