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
use App\Properties\Base\BaseSendPredictorEmailsProperty;
use App\Utils\AppMode;
use LogicException;
use App\Slim\Model\User\QMUser;
class UserSendPredictorEmailsProperty extends BaseSendPredictorEmailsProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;

    /**
     * @param QMUser $user
     * @param bool $sendPredictorEmails
     * @return bool|int
     */
    public static function setSendPredictorEmails($user, $sendPredictorEmails)
    {
        if ($sendPredictorEmails === $user->sendPredictorEmails) {
            if (AppMode::isProduction()) {
                QMLog::error('Not setting setSendPredictorEmails because it has not changed.');
            }
            return true;
        }
        if ($sendPredictorEmails === null) {
            throw new LogicException('sendPredictorEmails not provided to setSendPredictorEmails');
        }
        if ($user === null) {
            throw new LogicException('user not provided to setSendPredictorEmails');
        }
        $sendPredictorEmails = filter_var((string)$sendPredictorEmails, FILTER_VALIDATE_BOOLEAN);
        if ($sendPredictorEmails !== true && $sendPredictorEmails !== false) {
            throw new BadRequestException('sendPredictorEmails should be true or false');
        }
        if ($sendPredictorEmails === false) {
            QMLog::errorIfNotTesting('User un-subscribed from PredictorEmails', ['user' => $user]);
        }
        return $user->updateDbRow(['send_predictor_emails' => $sendPredictorEmails]);
    }
}
