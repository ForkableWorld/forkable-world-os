<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use LogicException;

/** @package App\Exceptions
 */
class DuplicateNotificationException extends LogicException {
    /**
     * @param string $message
     */
    public function __construct(string $message){
        parent::__construct($message);
    }
}
