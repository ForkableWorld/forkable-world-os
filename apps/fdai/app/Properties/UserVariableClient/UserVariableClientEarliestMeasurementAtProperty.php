<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableClient;
use App\Models\UserVariableClient;
use App\Traits\PropertyTraits\UserVariableClientProperty;
use App\Properties\Base\BaseEarliestMeasurementAtProperty;
use App\Traits\PropertyTraits\IsTemporal;
class UserVariableClientEarliestMeasurementAtProperty extends BaseEarliestMeasurementAtProperty
{
    use UserVariableClientProperty, IsTemporal;
    public $table = UserVariableClient::TABLE;
    public $parentClass = UserVariableClient::class;
}
