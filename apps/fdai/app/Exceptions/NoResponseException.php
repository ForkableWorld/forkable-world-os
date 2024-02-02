<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
class NoResponseException extends QMException {
    /**
     * NoResponseException constructor.
     * @param string $string
     */
    public function __construct(string $string){
        parent::__construct(QMException::CODE_INTERNAL_SERVER_ERROR, $string);
    }
}
