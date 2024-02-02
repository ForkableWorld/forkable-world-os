<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connection;
use App\Models\Connection;
use App\Traits\PropertyTraits\ConnectionProperty;
use App\Properties\Base\BaseUpdateErrorProperty;
use Illuminate\Http\Request;
use App\Fields\Field;
class ConnectionUpdateErrorProperty extends BaseUpdateErrorProperty
{
    use ConnectionProperty;
    public $table = Connection::TABLE;
    public $parentClass = Connection::class;
    public function getDetailsField($resolveCallback = null, string $name = null): Field{
        return parent::getDetailsField($resolveCallback, $name)
            ->hideFromDetail(function($value, $resource){
                return empty($value);
            });
    }
}
