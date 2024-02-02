<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Logging\QMLog;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseEarliestReminderTimeProperty;
use App\Traits\PropertyTraits\IsTime;
use LogicException;
use App\Slim\Model\User\QMUser;
class UserEarliestReminderTimeProperty extends BaseEarliestReminderTimeProperty
{
    use UserProperty;
    use IsTime;
    public $table = User::TABLE;
    public $parentClass = User::class;
    public static function fixInvalidRecords(): void {
        \App\Logging\ConsoleLog::info(__FUNCTION__);
	    /** @var User[] $users */
	    $users = User::query()
	                 ->where(User::FIELD_EARLIEST_REMINDER_TIME, '>', QMUser::DEFAULT_EARLIEST_REMINDER_TIME)
	                 ->get();
        foreach ($users as $user) {
            $user->fixIfDifferenceBetweenEarliestAndLatestTimesIsLessThanTwelveHours();
        }
	    /** @var User[] $users */
        $users = User::query()
            ->where(User::FIELD_LATEST_REMINDER_TIME, '<', QMUser::DEFAULT_LATEST_REMINDER_TIME)
            ->get();
        foreach ($users as $user) {
            $user->fixIfDifferenceBetweenEarliestAndLatestTimesIsLessThanTwelveHours();
        }
    }
    /**
     * @param QMUser $user
     * @param string $earliestReminderTime
     * @return int
     *
     */
    public static function setEarliestReminderTime($user, $earliestReminderTime)
    {
        if (!$user) {
            throw new LogicException('setEarliestReminderTime requires $user object');
        }
        if (!$earliestReminderTime) {
            throw new BadRequestException('setEarliestReminderTime requires $earliestReminderTime object');
        }
        /** @noinspection NotOptimalRegularExpressionsInspection */
        if (!preg_match("/(2[0-4]|[01][0-9]):([0-5][0-9]):([0-5][0-9])/", $earliestReminderTime)) {
            throw new BadRequestException('earliestReminderTime must be provided in HH:mm:ss format');
        }
        $earliestReminderTime = substr_replace($earliestReminderTime, "00", -2);
        return $user->updateDbRow(['earliest_reminder_time' => $earliestReminderTime]);
    }
}
