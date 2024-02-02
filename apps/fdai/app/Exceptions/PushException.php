<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use LogicException;
use App\Slim\Model\Notifications\PushNotification;
use Throwable;
class PushException extends LogicException {
    /**
     * PushException constructor.
     * @param string $message
     * @param PushNotification $pushNotification
     * @param Throwable|null $previous
     */
    public function __construct(string $message, PushNotification $pushNotification, Throwable $previous = null){
        $pushNotification->logError($message);
        parent::__construct($pushNotification->__toString(), 500);
    }
}
