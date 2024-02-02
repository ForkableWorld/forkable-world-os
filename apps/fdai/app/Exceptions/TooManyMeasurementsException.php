<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
class TooManyMeasurementsException extends QMException {
    /**
     * @param string $message
     */
    public function __construct(string $message){
        parent::__construct(400, $message, null, null, false);
    }
}
