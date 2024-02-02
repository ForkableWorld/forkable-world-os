<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Traits\QMAnalyzableTrait;
use Exception;
class AlreadyAnalyzingException extends Exception {
    /**
     * AccessTokenExpiredException constructor.
     * @param QMAnalyzableTrait $object
     */
    public function __construct($object){
        parent::__construct("Already analyzing $object");
    }
}
