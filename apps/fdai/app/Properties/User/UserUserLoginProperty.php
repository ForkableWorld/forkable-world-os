<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Exceptions\InvalidUsernameException;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseUserLoginProperty;
class UserUserLoginProperty extends BaseUserLoginProperty
{
    use UserProperty;
    public const USERNAME_SYSTEM = "system";
    public const USERNAME_DEMO = "demo";
    public const USERNAME_MIKE = "mike";
    public $table = User::TABLE;
    public $parentClass = User::class;
    public function cannotBeChangedToNull(): bool{
        return true;
    }
    public function validate(): void {
        parent::validate();
        $val = $this->getDBValue();
        if(empty($val)){
            $this->throwException("should not be empty");
        }
    }
}
