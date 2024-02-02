<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;
use App\Slim\Model\User\PublicUser;
class NoEmailAddressException extends Exception {
    /**
     * NoEmailAddressException constructor.
     * @param PublicUser $user
     */
    public function __construct(PublicUser $user){
        parent::__construct("$user has not provided an email address! ");
    }
}
