<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
class InsufficientScopeException extends QMException {
    /**
     * InsufficientScopeException constructor.
     * @param string $message
     */
    public function __construct(string $message){
        parent::__construct(403, $message);
    }
}
