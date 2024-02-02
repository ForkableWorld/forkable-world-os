<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Measurement;
use App\Models\Measurement;
use App\Traits\PropertyTraits\MeasurementProperty;
use App\Properties\Base\BaseConnectorIdProperty;
use App\DataSources\QMConnector;
class MeasurementConnectorIdProperty extends BaseConnectorIdProperty
{
    use MeasurementProperty;
    public $table = Measurement::TABLE;
    public $parentClass = Measurement::class;
    public static function getDefault($data = null){
        if($c = QMConnector::getCurrentlyImportingConnector()){
            return $c->id;
        }
        return null;
    }
}
