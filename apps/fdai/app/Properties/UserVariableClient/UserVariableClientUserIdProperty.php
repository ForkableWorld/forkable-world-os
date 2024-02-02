<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariableClient;
use App\Models\UserVariableClient;
use App\Traits\HasUserFilter;
use App\Traits\PropertyTraits\UserVariableClientProperty;
use App\Properties\Base\BaseUserIdProperty;
class UserVariableClientUserIdProperty extends BaseUserIdProperty
{
    use UserVariableClientProperty;
    use HasUserFilter;
    public $table = UserVariableClient::TABLE;
    public $parentClass = UserVariableClient::class;
}
