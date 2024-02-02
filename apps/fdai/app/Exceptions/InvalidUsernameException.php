<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use RuntimeException;

class InvalidUsernameException extends RuntimeException {
    /**
     * InvalidUsernameException constructor.
     * @param string $username
     * @param string|null $message
     */
    public function __construct(string $username, string $message = null){
        $message = "$username is not a valid username.  Username can only contain letters and numbers. $message";
        parent::__construct($message, 400);
    }
}
