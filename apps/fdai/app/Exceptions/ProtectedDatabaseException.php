<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;

class ProtectedDatabaseException extends Exception {
    /**
     * UnauthorizedException constructor.
     * @param string $message
     */
    public function __construct(string $message){
        parent::__construct($message, 401);
    }
}
