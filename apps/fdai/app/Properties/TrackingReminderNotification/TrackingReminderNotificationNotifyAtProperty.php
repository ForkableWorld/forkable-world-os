<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\TrackingReminderNotification;
use App\Exceptions\UserNotFoundException;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
use App\Properties\TrackingReminder\TrackingReminderLatestTrackingReminderNotificationNotifyAtProperty;
use App\Traits\HasFilter;
use App\Traits\PropertyTraits\IsDateTime;
use App\Traits\PropertyTraits\TrackingReminderNotificationProperty;
use App\Properties\Base\BaseNotifyAtProperty;
use App\Types\TimeHelper;
use App\Fields\Field;
class TrackingReminderNotificationNotifyAtProperty extends BaseNotifyAtProperty
{
    use TrackingReminderNotificationProperty, IsDateTime, HasFilter;
    const OPTION_DUE = "Past Due";
    const OPTION_ALL = "All";
    const OPTION_UPCOMING= "Upcoming";
    const OPTION_TODAY= "Today";
    public $table = TrackingReminderNotification::TABLE;
    public $parentClass = TrackingReminderNotification::class;
    /**
     * @param TrackingReminder $r
     * @return array
     * @throws UserNotFoundException
     */
    public static function generate(TrackingReminder $r): array{
        $times = [];
        $u = $r->getUser();
        $freq = $r->reminder_frequency;
        $earliestReminderTime = $u->earliest_reminder_time;
        $latestReminderTime = $u->latest_reminder_time;
		if(!$freq){le('!$freq');}
		if(!$earliestReminderTime){le('!$earliestReminderTime');}
		if(!$latestReminderTime){le('!$latestReminderTime');}
        $notifyAt = $r->getEarliestNotificationCutoffAt();
        $intraDay = $freq < 86400;
        $freqDesc = $r->getFrequencyDescription();
        $timezone = $u->getTimezoneIfSet() ?? "UTC";
        $cutoffAt = $r->getLatestNotificationCutoffAt();
        $timeThatNotificationsMightBeDisabled = 12*3600; //
        while (strtotime($notifyAt)-($freq+$timeThatNotificationsMightBeDisabled) < strtotime($cutoffAt)) {
            // -$freq is to add one extra so the database latest_tracking_reminder_notification_notify_at is above the cutoff
            // and we don't keep fetching reminders that are already up to date when generating
            $localHis = $u->utcToLocalHis(strtotime($notifyAt));
            $YMD = TimeHelper::YYYYmmddd(strtotime($notifyAt));
            if ($u->hasTimeZone() && $localHis < $earliestReminderTime) {
                if ($intraDay) {
                    $notifyAt = db_date(strtotime($notifyAt) + $freq);
                    continue;
                } else {
                    $r->logInfo("Creating notification before earliestReminderTime $earliestReminderTime " .
                        "\nat $localHis on $YMD \nbecause the frequency is $freqDesc \nTimezone: $timezone\n");
                }
            }
            if ($u->hasTimeZone() && $localHis > $latestReminderTime) {
                if ($intraDay) {
                    $notifyAt = db_date(strtotime($notifyAt) + $freq);
                    continue;
                } else {
                    $r->logInfo("Creating notification after latestReminderTime $latestReminderTime " .
                        "\nat $localHis on $YMD \nbecause the frequency is $freqDesc \nTimezone: $timezone\n");
                }
            }
            $r->logDebug("Creating notification at $localHis" .
                " | latest allowed: $latestReminderTime | earliest allowed: $earliestReminderTime ");
            $localNotificationTimes[] = $localHis;
            $times[] = $notifyAt;
            $notifyAt = db_date(strtotime($notifyAt) + $freq);
        }
        return $times;
    }
    public function showOnIndex(): bool {return true;}
    public function showOnUpdate(): bool {return true;}
    public function showOnCreate(): bool {return true;}
    public function showOnDetail(): bool {return true;}
    public function getFilterOptions(): array{
        return [
            self::OPTION_DUE => self::OPTION_DUE,
            self::OPTION_ALL => self::OPTION_ALL,
            self::OPTION_UPCOMING => self::OPTION_UPCOMING,
            self::OPTION_TODAY => self::OPTION_TODAY,
        ];
    }
    /**
     * @param $query
     * @param $type
     * @return mixed
     */
    public function applyFilter($query, $type){
        if($type === self::OPTION_DUE){
            $query->where($this->table.'.'.$this->name, "<", now_at());
        }
        if($type === self::OPTION_UPCOMING){
            $query->where($this->table.'.'.$this->name, ">", now_at());
        }
        if($type === self::OPTION_TODAY){
            $query->where($this->table.'.'.$this->name, ">", db_date(time()-16*3600));
            $query->where($this->table.'.'.$this->name, "<", db_date(time()+16*3600));
        }
        return $query;
    }
    /**
     * Set the default options for the filter.
     *
     * @return string
     */
    public function defaultFilter(): string{
        return self::OPTION_DUE;
    }
    public function getField($resolveCallback = null, string $name = null): Field{
        return $this->getDayOfWekTimeField($resolveCallback, $name);
    }
    public function getLatestUnixTime(): int {
        return time() + TrackingReminderLatestTrackingReminderNotificationNotifyAtProperty::MAX_FUTURE_SECONDS;
    }
}
