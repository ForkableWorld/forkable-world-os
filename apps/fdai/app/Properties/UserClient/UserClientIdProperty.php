<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserClient;
use App\Traits\PropertyTraits\IsPrimaryKey;
use App\Models\UserClient;
use App\Traits\PropertyTraits\UserClientProperty;
use App\Properties\Base\BaseIntegerIdProperty;
class UserClientIdProperty extends BaseIntegerIdProperty{
	use IsPrimaryKey;
    use UserClientProperty;
    public $table = UserClient::TABLE;
    public $parentClass = UserClient::class;
    public $autoIncrement = true;
    public $isPrimary = true;
}
