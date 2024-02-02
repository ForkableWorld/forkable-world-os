<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Astral\Filters;
use App\Models\TrackingReminder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use App\Filters\Filter;
class ReminderNotificationsEnabledFilter extends Filter {
	const ENABLED = 'enabled';
	const DISABLED = 'disabled';
	const ALL = 'all';
	public $name = "Notifications";
	/**
	 * The filter's component.
	 * @var string
	 */
	public $component = 'select-filter';
	/**
	 * Apply the filter to the given query.
	 * @param Request $request
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param mixed $value
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function apply(Request $request, $query, $value){
		if($value === self::ENABLED){
			$query->where(TrackingReminder::FIELD_REMINDER_FREQUENCY, ">", 0)->where(function($q){
					/** @var Builder $q */
					$q->where(TrackingReminder::FIELD_STOP_TRACKING_DATE, ">", now_at())
						->orWhereNull(TrackingReminder::FIELD_STOP_TRACKING_DATE);
				});
		} elseif($value === self::DISABLED){
			$query->where(function($q){
				/** @var Builder $q */
				$q->orWhere(TrackingReminder::FIELD_STOP_TRACKING_DATE, "<", now_at())
					->orWhere(TrackingReminder::FIELD_REMINDER_FREQUENCY, 0);
			});
		}
		//$sql = DBQueryLogServiceProvider::toSQL($query);
		return $query;
	}
	/**
	 * Get the filter's available options.
	 * @param Request $request
	 * @return array
	 */
	public function options(Request $request){
		return [
			'Enabled' => self::ENABLED,
			'Disabled' => self::DISABLED,
			'All' => self::DISABLED,
		];
	}
	public function default(){
		return self::ENABLED;
	}
}
