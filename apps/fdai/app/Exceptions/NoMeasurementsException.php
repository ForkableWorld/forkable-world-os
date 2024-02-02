<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Variables\QMVariable;
class NoMeasurementsException extends NotEnoughMeasurementsException {
    /**
     * @param QMVariable $variable
     * @param string $problemDetails
     */
    public function __construct(QMVariable $variable, string $problemDetails = ''){
        parent::__construct($variable,$problemDetails, "No Measurements");
    }
}
