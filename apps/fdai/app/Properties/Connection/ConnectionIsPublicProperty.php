<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connection;
use App\Models\Connection;
use App\Traits\PropertyTraits\ConnectionProperty;
use App\Properties\Base\BaseIsPublicProperty;
use App\Types\BoolHelper;
class ConnectionIsPublicProperty extends BaseIsPublicProperty
{
    use ConnectionProperty;
    public $table = Connection::TABLE;
    public $parentClass = Connection::class;
    /**
     * Set the default options for the filter.
     *
     * @return string
     */
    public function defaultFilter(): string{return BoolHelper::ALL_STRING;}
    public function showOnIndex(): bool{return false;}
}
