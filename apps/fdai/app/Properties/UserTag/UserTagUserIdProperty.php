<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserTag;
use App\Models\UserTag;
use App\Traits\HasUserFilter;
use App\Traits\PropertyTraits\UserTagProperty;
use App\Properties\Base\BaseUserIdProperty;
class UserTagUserIdProperty extends BaseUserIdProperty
{
    use UserTagProperty;
    use HasUserFilter;
    public $table = UserTag::TABLE;
    public $parentClass = UserTag::class;
}
