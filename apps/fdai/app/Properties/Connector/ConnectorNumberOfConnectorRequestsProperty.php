<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connector;
use App\Models\Connector;
use App\Traits\PropertyTraits\ConnectorProperty;
use App\Properties\Base\BaseNumberOfConnectorRequestsProperty;
class ConnectorNumberOfConnectorRequestsProperty extends BaseNumberOfConnectorRequestsProperty
{
    use ConnectorProperty;
    public $table = Connector::TABLE;
    public $parentClass = Connector::class;
}
