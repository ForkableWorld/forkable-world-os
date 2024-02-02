<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
class DuplicateDataException extends QMException {
    /**
     * @param string $message
     */
    public function __construct(string $message){
        parent::__construct(QMException::CODE_BAD_REQUEST, $message, [], null, false);
    }
}
