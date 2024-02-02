<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use Illuminate\Support\Carbon;
trait FormatsDate {
	/**
	 * All the fields other than eloquent model dates array you want to format
	 * @var array
	 */
	protected $formattedDates = [];
	/**
	 * Flag to disable formatting on demand
	 * @var bool
	 */
	protected $noFormat = false;
	/**
	 * Prefix which will be added to the fields for formatted date
	 * @var string
	 */
	protected $formattedFieldPrefix = 'local_';
	/**
	 * Override the models toArray to append the formatted dates fields
	 * @return array
	 */
	public function toArray(){
		$data = parent::toArray();
		if($this->noFormat){
			return $data;
		}
		foreach($this->getFormattedDateFields() as $dateField){
			$data[$this->formattedFieldPrefix . $dateField] = $this->toDateObject($this->{$dateField});
		}
		return $data;
	}
	/**
	 * Format time part of timestamp
	 * @param $dateValue
	 * @return string|null
	 */
	private function formattedDate($dateValue){
		if($dateValue === null){
			return null;
		}
		return $this->inUsersTimezone($dateValue)->format(config('setting.date_format'));
	}
	/**
	 * Format date part of timestamp
	 * @param $dateValue
	 * @return string|null
	 */
	private function formattedTime($dateValue){
		if($dateValue === null){
			return null;
		}
		return $this->inUsersTimezone($dateValue)->format(config('setting.time_format'));
	}
	/**
	 * Format date diff for humans
	 * @param $dateValue
	 * @return string|null
	 */
	private function formattedDiffForHumans($dateValue){
		if($dateValue === null){
			return null;
		}
		return $this->inUsersTimezone($dateValue)->diffForHumans();
	}
	/**
	 * Built a date object for serialization
	 * @param $dateValue
	 * @return array
	 */
	private function toDateObject($dateValue): array{
		return [
			'date' => $this->formattedDate($dateValue),
			'time' => $this->formattedTime($dateValue),
			'for_human' => $this->formattedDiffForHumans($dateValue),
		];
	}
	/**
	 * Return all the fields which needed formatted dates
	 * @return mixed
	 */
	private function getFormattedDateFields(){
		return array_merge($this->formattedDates, $this->getDates());
	}
	/**
	 * Setter for formatted dates fields array
	 * @param array $formattedDates
	 */
	public function setFormattedDates(array $formattedDates){
		$this->formattedDates = $formattedDates;
	}
	/**
	 * Get the formatted date object for a field
	 * @param $field
	 * @return array
	 */
	public function toLocalTime($field = null){
		$dateValue = $this->{$field} === null ? Carbon::now() : $this->{$field};
		return $this->toDateObject($dateValue);
	}
	/**
	 * Disable formatting for the dates
	 * @return $this
	 */
	public function disableFormat(){
		$this->noFormat = true;
		return $this;
	}
	/**
	 * Enable formatting for the dates
	 * @return $this
	 */
	public function enableFormat(){
		$this->noFormat = false;
		return $this;
	}
	/**
	 * Get the timestamp in users timezone
	 * @param $dateValue
	 * @return Carbon
	 */
	private function inUsersTimezone($dateValue): Carbon{
		$timezone = optional(auth()->user())->timezone ?? config('app.timezone');
		return $this->asDateTime($dateValue)->timezone($timezone);
	}
}
