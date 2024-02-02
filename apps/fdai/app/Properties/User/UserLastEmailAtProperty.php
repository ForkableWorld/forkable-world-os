<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseLastEmailAtProperty;
class UserLastEmailAtProperty extends BaseLastEmailAtProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
}
