<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BasePushNotificationsEnabledProperty;
use LogicException;
use App\Slim\Model\User\QMUser;
class UserPushNotificationsEnabledProperty extends BasePushNotificationsEnabledProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
    /**
     * @param QMUser $user
     * @param bool $pushNotificationsEnabled
     * @return int
     *
     */
    public static function setPushNotificationsEnabled($user, $pushNotificationsEnabled)
    {
        if (!$user) {
            throw new LogicException('setPushNotificationsEnabled requires $user object');
        }
        if (!isset($pushNotificationsEnabled)) {
            throw new BadRequestException('setPushNotificationsEnabled requires $pushNotificationsEnabled object');
        }
        $pushNotificationsEnabled = filter_var((string)$pushNotificationsEnabled, FILTER_VALIDATE_BOOLEAN);
        if ($pushNotificationsEnabled !== true && $pushNotificationsEnabled !== false) {
            throw new BadRequestException('pushNotificationsEnabled should be true or false');
        }
        return $user->updateDbRow(['push_notifications_enabled' => $pushNotificationsEnabled]);
    }
}
