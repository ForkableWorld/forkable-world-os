<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
class UserEmailProperty extends UserUserEmailProperty
{
    public $name = User::FIELD_EMAIL;
    public function showOnIndex(): bool {return true;}
}
