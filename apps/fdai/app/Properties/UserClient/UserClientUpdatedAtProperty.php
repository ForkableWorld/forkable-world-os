<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserClient;
use App\Models\UserClient;
use App\Traits\PropertyTraits\UserClientProperty;
use App\Properties\Base\BaseUpdatedAtProperty;
class UserClientUpdatedAtProperty extends BaseUpdatedAtProperty
{
    use UserClientProperty;
    public $table = UserClient::TABLE;
    public $parentClass = UserClient::class;
}
