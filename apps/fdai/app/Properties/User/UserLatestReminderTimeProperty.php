<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseLatestReminderTimeProperty;
use LogicException;
use App\Slim\Model\User\QMUser;

class UserLatestReminderTimeProperty extends BaseLatestReminderTimeProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;

    /**
     * @param QMUser $user
     * @param string $latestReminderTime
     * @return bool
     */
    public static function setLatestReminderTime($user, $latestReminderTime): bool{
        if (!$user) {
            throw new LogicException('setLatestReminderTime requires $user object');
        }
        if (!$latestReminderTime) {
            throw new BadRequestException('setLatestReminderTime requires $latestReminderTime object');
        }
        /** @noinspection NotOptimalRegularExpressionsInspection */
        if (!preg_match("/(2[0-4]|[01][1-9]|10):([0-5][0-9]):([0-5][0-9])/", $latestReminderTime)) {
            throw new BadRequestException('latestReminderTime must be provided in HH:mm:ss format');
        }
        $latestReminderTime = substr_replace($latestReminderTime, "00", -2);
        return $user->updateDbRow(['latest_reminder_time' => $latestReminderTime]);
    }
}
