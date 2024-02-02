<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Connection;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\Connection;
use App\Traits\PropertyTraits\ConnectionProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class ConnectionIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use ConnectionProperty;
    public $table = Connection::TABLE;
    public $parentClass = Connection::class;
    public $isPrimary = true;
    public $autoIncrement = true;
    public const SYNONYMS = [
        'connection_id',
        'id',
    ];
}
