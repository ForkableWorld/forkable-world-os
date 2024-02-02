<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableClient;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\UserVariableClient;
use App\Traits\PropertyTraits\UserVariableClientProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class UserVariableClientIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use UserVariableClientProperty;
    public $table = UserVariableClient::TABLE;
    public $parentClass = UserVariableClient::class;
    public $autoIncrement = true;
    public $isPrimary = true;
}
