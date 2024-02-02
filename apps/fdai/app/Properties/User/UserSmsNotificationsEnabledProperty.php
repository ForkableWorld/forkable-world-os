<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseSmsNotificationsEnabledProperty;
use LogicException;
use App\Slim\Model\User\QMUser;
class UserSmsNotificationsEnabledProperty extends BaseSmsNotificationsEnabledProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;

    /**
     * @param QMUser $user
     * @param bool $smsNotificationsEnabled
     * @return int
     *
     */
    public static function setSmsNotificationsEnabled($user, $smsNotificationsEnabled)
    {
        if (!$user) {
            throw new LogicException('setSmsNotificationsEnabled requires $user object');
        }
        if (!isset($smsNotificationsEnabled)) {
            throw new BadRequestException('setSmsNotificationsEnabled requires $smsNotificationsEnabled object');
        }
        $smsNotificationsEnabled = filter_var((string)$smsNotificationsEnabled, FILTER_VALIDATE_BOOLEAN);
        if ($smsNotificationsEnabled !== true && $smsNotificationsEnabled !== false) {
            throw new BadRequestException('smsNotificationsEnabled should be true or false');
        }
        return $user->updateDbRow(['sms_notifications_enabled' => $smsNotificationsEnabled]);
    }
}
