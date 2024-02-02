<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminder;
use App\Exceptions\UserNotFoundException;
use App\InputFields\FrequencySelectorField;
use App\Logging\QMLog;
use App\Models\TrackingReminder;
use App\Storage\DB\Writable;
use App\Storage\QueryBuilderHelper;
use App\Traits\PropertyTraits\TrackingReminderProperty;
use App\Properties\Base\BaseReminderFrequencyProperty;
use App\Types\TimeHelper;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Fields\Field;
class TrackingReminderReminderFrequencyProperty extends BaseReminderFrequencyProperty
{
    use TrackingReminderProperty;
    public $order = "02";
    public $description = 'How often you want to track';
    public $table = TrackingReminder::TABLE;
    public $parentClass = TrackingReminder::class;
	public $required = true;
    public const SYNONYMS = [
        'frequency',
        TrackingReminder::FIELD_REMINDER_FREQUENCY,
    ];
	/**
	 * @param null $data
	 * @return int
	 */
	public static function getDefault($data = null): int{
        return static::DEFAULT;
    }
	/**
	 * @param \Illuminate\Support\Collection|TrackingReminder[] $ids
	 * @param int $freq
	 * @return array
	 */
    protected static function reduceFreqToWeeklyOrDelete(Collection $ids, int $freq): array{
        $reminders = [];
        Writable::statementStatic('
            update ignore `tracking_reminders` set reminder_frequency = '.$freq.'
                where `deleted_at` is null
                  and `reminder_frequency` > 0
                  and start_tracking_date <= DATE(NOW())
                  and (stop_tracking_date > DATE(NOW()) or `stop_tracking_date` is null)
                  and `created_at` < (NOW() - INTERVAL 3 MONTH)
                  and (`last_tracked` is null or `last_tracked` < (NOW() - INTERVAL 3 MONTH))
                  and `reminder_frequency` < '.$freq.'
        ');
        foreach($ids as $id){
            $tr = TrackingReminder::find($id);
            try {
                $msg = "Changing frequency from $tr->reminder_frequency to $freq for ".$tr->getUser().
                    " because they last tracked ".TimeHelper::timeSinceHumanString($tr->last_tracked);
            } catch (UserNotFoundException $e){
                $tr->logError(__METHOD__.": ".$e->getMessage());
                continue;
            }
            $reminders[$msg] = $tr;
            try {
                $tr->logInfo($msg);
                $tr->update([self::NAME => $freq]);
            } catch (QueryException $e) {
				try {
					$tr->logInfo($tr->getUser().":".$e->getMessage());
				} catch (UserNotFoundException $e) {
					$tr->logError(__METHOD__.": ".$e->getMessage());
				}
                $tr->forceDelete();
            }
        }
        return $reminders;
    }
    public function cannotBeChangedToNull(): bool{
        return true;
    }
	/**
	 * @param $data
	 * @return mixed|null
	 */
	public static function pluckOrDefault($data){
        return parent::pluckOrDefault($data);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getIndexField($resolveCallback = null, string $name = null): Field{
        return $this->getTextField($name, function($value, $resource, $attribute){
            /** @var TrackingReminder $resource */
            return $resource->getHumanizedFrequency();
        })->updateLink();
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getUpdateField($resolveCallback = null, string $name = null): Field{
        return $this->getFrequencySelector($name, $resolveCallback);
    }
	/**
	 * @param null $resolveCallback
	 * @param string|null $name
	 * @return \App\Fields\Field
	 */
	public function getCreateField($resolveCallback = null, string $name = null): Field{
        return $this->getFrequencySelector($name, $resolveCallback);
    }
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnIndex(): bool {return true;}
    public function showOnDetail(): bool {return true;}
    public static function reduceFrequencyForInactiveUsers(): array{
		le("don't do this until you implement a fix on login or figure out a better method");
        $freq = 7*86400;
        $qb = TrackingReminder::whereActiveCreatedAMonthAgo()
            ->where(TrackingReminder::FIELD_REMINDER_FREQUENCY, "<", $freq);
            //->where(TrackingReminder::FIELD_USER_ID, 230)
        $sql = QueryBuilderHelper::toPreparedSQL($qb);
        $ids = $qb->pluck('id');
        $num = count($ids);
        QMLog::info("$num last tracked more than a month ago. \n".$qb->toSql());
        return self::reduceFreqToWeeklyOrDelete($ids, $freq);
    }
    /**
     * @param string|null $name
     * @param $resolveCallback
     * @return FrequencySelectorField
     */
    protected function getFrequencySelector(?string $name, $resolveCallback): FrequencySelectorField{
        return new FrequencySelectorField($name ?? $this->getTitleAttribute(), $this->name,
            $resolveCallback);
    }
	public function validate(): void{
		parent::validate();
	}
}
