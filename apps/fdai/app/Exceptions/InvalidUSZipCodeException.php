<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use Exception;
class InvalidUSZipCodeException extends Exception {
    /**
     * InvalidZipCodeException constructor.
     * @param string $string
     */
    public function __construct(string $string){
        parent::__construct($string);
    }
}
