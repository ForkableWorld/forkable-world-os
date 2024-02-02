<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\HasUserFilter;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseUserIdProperty;
class UserVariableUserIdProperty extends BaseUserIdProperty
{
    use UserVariableProperty;
    use HasUserFilter;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
}
