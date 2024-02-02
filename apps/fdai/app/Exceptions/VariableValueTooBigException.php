<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\Solutions\DeleteLargeMeasurementsSolution;
use Exception;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use App\Variables\QMVariable;
class VariableValueTooBigException extends Exception implements ProvidesSolution
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
        $maxInCommonUnit = $v->getMaximumAllowedValueAttribute();
        $commonUnit = $v->getCommonUnit();
        $maximumAllowedValue = $v->maximumAllowedValue;
        $maximumAllowedValueForVariableOrUnitInCommonUnit = $v->getMaximumAllowedValueAttribute();
        $commonMaximumAllowedValueInCommonUnit = $v->maximumAllowedValue;
        $url = $v->getDeleteLargeMeasurementsUrl();
        parent::__construct("$valueInCommonUnit too big for $this.  Maximum value is $maxInCommonUnit $commonUnit.
                $type value $valueInCommonUnit $commonUnit->abbreviatedName
                exceeds maximum $maxInCommonUnit $commonUnit->abbreviatedName
                for variable $v->name
                maximumAllowedValue = $maximumAllowedValue;
                maximumAllowedValueForVariableOrUnitInCommonUnit = $maximumAllowedValueForVariableOrUnitInCommonUnit;
                commonMaximumAllowedValueInCommonUnit = $commonMaximumAllowedValueInCommonUnit;

               $url
                ");
    }
    /**
     * @return DeleteLargeMeasurementsSolution
     */
    public function getSolution(): Solution{
        return new DeleteLargeMeasurementsSolution($this->variable);
    }
}
