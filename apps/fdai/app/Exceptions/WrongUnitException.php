<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Exceptions;
use App\Models\Measurement;
use App\Properties\Measurement\MeasurementUnitIdProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\Solutions\ViewMeasurementsSolution;
use App\Variables\QMUserVariable;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
class WrongUnitException extends \Exception implements ProvidesSolution
{
    /**
     * @var QMMeasurement
     */
    private $measurement;
    public function __construct(QMMeasurement $measurement, QMUserVariable $variable){
        $commonUnitId = $variable->getCommonUnitId();
        $measurementUnit = $measurement->getQMUnit();
        $commonUnit = QMUnit::find($commonUnitId);
        $this->measurement = $measurement;
        $startAt = $measurement->getStartAt();
        parent::__construct("Measurement from $startAt unit $measurementUnit->name is not in common unit ".
            "$commonUnit->name! You can fix it at ".$measurement->getEditUrl());
    }
    public function getSolution(): Solution{
        $m = $this->measurement;
        return new ViewMeasurementsSolution([
            Measurement::FIELD_VARIABLE_ID => $m->variableId,
            Measurement::FIELD_UNIT_ID => $m->unitId,
        ], $m->getUrl(),
            MeasurementUnitIdProperty::generateFixInvalidRecordsUrl());
    }
}
