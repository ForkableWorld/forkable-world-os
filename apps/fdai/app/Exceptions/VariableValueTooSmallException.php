<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\DeleteLargeMeasurementsSolution;
use App\Solutions\DeleteSmallMeasurementsSolution;
use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use App\Variables\QMVariable;
class VariableValueTooSmallException extends Exception implements ProvidesSolution
{
    /**
     * @var QMVariable
     */
    private $variable;
    /**
     * VariableValueTooBigException constructor.
     * @param QMVariable $v
     * @param float $valueInCommonUnit
     * @param string $type
     */
    public function __construct($v, float $valueInCommonUnit, string $type){
        $this->variable = $v;
        $commonUnit = $v->getCommonUnit();
        $min = $v->getMinimumAllowedValueAttribute();
        $url = $v->getViewSmallMeasurementsUrl();
        parent::__construct("$valueInCommonUnit too small for $this.  Minimum value is $min $commonUnit
        $type value $valueInCommonUnit $commonUnit->abbreviatedName
                is below minimum $min $commonUnit->abbreviatedName
                for variable $v->name

                View and delete at:
                 $url
                \";");
    }
    /**
     * @return DeleteLargeMeasurementsSolution
     */
    public function getSolution(): Solution{
        return new DeleteSmallMeasurementsSolution($this->variable);
    }
}
