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
use App\Properties\Base\BaseGetPreviewBuildsProperty;
use LogicException;
use App\Slim\Model\User\QMUser;
class UserGetPreviewBuildsProperty extends BaseGetPreviewBuildsProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;

    /**
     * @param QMUser $user
     * @param bool $getPreviewBuilds
     * @return bool|int
     *
     */
    public static function setGetPreviewBuilds($user, $getPreviewBuilds)
    {
        if ($getPreviewBuilds === null) {
            throw new LogicException('getPreviewBuilds not provided to setGetPreviewBuilds');
        }
        if ($user === null) {
            throw new LogicException('user not provided to setGetPreviewBuilds');
        }
        $getPreviewBuilds = filter_var((string)$getPreviewBuilds, FILTER_VALIDATE_BOOLEAN);
        if ($getPreviewBuilds !== true && $getPreviewBuilds !== false) {
            throw new BadRequestException('getPreviewBuilds should be true or false');
        }
        if ($getPreviewBuilds === false) {
            QMLog::error('User unsubscribed from PreviewBuilds', ['user' => $user]);
        }
        return $user->updateDbRow(['get_preview_builds' => $getPreviewBuilds]);
    }
}
