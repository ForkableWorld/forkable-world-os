<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Traits\QMAnalyzableTrait;
use Exception;
use App\Slim\Model\DBModel;
class AlreadyAnalyzedException extends Exception {
    /**
     * @param QMAnalyzableTrait|DBModel $object
     */
    public function __construct($object){
        parent::__construct("Already analyzed this instance of $object");
    }
}
