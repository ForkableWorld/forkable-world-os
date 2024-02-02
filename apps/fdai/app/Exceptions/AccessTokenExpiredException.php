<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Slim\Middleware\QMAuth;
class AccessTokenExpiredException extends UnauthorizedException {
    /**
     * AccessTokenExpiredException constructor.
     * @param string $message
     */
    public function __construct(string $message = "Access token expired"){
        QMAuth::logout(static::class); // Prevent trying to check auth again
        parent::__construct($message);
    }
}
