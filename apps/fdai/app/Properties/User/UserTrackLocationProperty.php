<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\BadRequestException;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseTrackLocationProperty;
use LogicException;
use App\Slim\Model\User\QMUser;
class UserTrackLocationProperty extends BaseTrackLocationProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;

    /**
     * @param QMUser $user
     * @param bool $trackLocation
     * @return bool|int
     *
     */
    public static function setTrackLocation($user, $trackLocation)
    {
        if ($trackLocation === null) {
            throw new LogicException('trackLocation not provided to setTrackLocation');
        }
        if ($user === null) {
            throw new LogicException('user not provided to setTrackLocation');
        }
        $trackLocation = filter_var((string)$trackLocation, FILTER_VALIDATE_BOOLEAN);
        if ($trackLocation !== true && $trackLocation !== false) {
            throw new BadRequestException('trackLocation should be true or false');
        }
        return $user->updateDbRow(['track_location' => $trackLocation]);
    }
}
