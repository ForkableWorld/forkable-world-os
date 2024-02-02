<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\ConnectorRequest;
use App\Models\ConnectorRequest;
use App\Traits\PropertyTraits\ConnectorRequestProperty;
use App\Properties\Base\BaseContentTypeProperty;
class ConnectorRequestContentTypeProperty extends BaseContentTypeProperty
{
    use ConnectorRequestProperty;
    public $table = ConnectorRequest::TABLE;
    public $parentClass = ConnectorRequest::class;
}
