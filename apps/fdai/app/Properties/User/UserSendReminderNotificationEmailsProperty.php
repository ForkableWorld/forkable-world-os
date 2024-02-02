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
use App\Properties\Base\BaseSendReminderNotificationEmailsProperty;
use LogicException;
use App\Slim\Model\User\QMUser;
class UserSendReminderNotificationEmailsProperty extends BaseSendReminderNotificationEmailsProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;

    /**
     * @param QMUser $user
     * @param bool $sendReminderNotificationEmails
     * @return bool|int
     *
     */
    public static function setSendReminderNotificationEmails($user, $sendReminderNotificationEmails)
    {
        if ($sendReminderNotificationEmails === null) {
            throw new LogicException('sendReminderNotificationEmails not provided to setSendReminderNotificationEmails');
        }
        if ($user === null) {
            throw new LogicException('user not provided to setSendReminderNotificationEmails');
        }
        $sendReminderNotificationEmails = filter_var((string)$sendReminderNotificationEmails, FILTER_VALIDATE_BOOLEAN);
        if ($sendReminderNotificationEmails !== true && $sendReminderNotificationEmails !== false) {
            throw new BadRequestException('sendReminderNotificationEmails should be true or false');
        }
        if ($sendReminderNotificationEmails === false) {
            QMLog::error('User unsubscribed from ReminderNotificationEmails', ['user' => $user]);
        }
        return $user->updateDbRow(['send_reminder_notification_emails' => $sendReminderNotificationEmails]);
    }
}
