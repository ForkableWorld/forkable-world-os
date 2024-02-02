<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
class NotEnoughMeasurementsException extends NotEnoughDataException {
    /**
     * @var QMUserVariable
     */
    protected $variable;
    /**
     * @param QMVariable $variable
     * @param string $problemDetails
     * @param string $title
     */
    public function __construct(QMVariable $variable, string $problemDetails = '', string $title = "Not Enough Measurements"){
        $this->analyzable = $this->variable = $variable;
        if(!empty($problemDetails)){$problemDetails .= "\n";}
        $problemDetails .= $variable->getMeasurementQuantitySentence();
        parent::__construct($variable, $title, $problemDetails);
    }
}
