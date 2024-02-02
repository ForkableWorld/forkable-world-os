<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseNumberOfStudiesProperty;
class UserNumberOfStudiesProperty extends BaseNumberOfStudiesProperty
{
    use UserProperty;
    public $table = User::TABLE;
    public $parentClass = User::class;
}
