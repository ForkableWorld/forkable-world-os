<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\User;
use App\Models\User;
use App\Traits\PropertyTraits\UserProperty;
use App\Properties\Base\BaseTimezoneProperty;
class UserTimezoneProperty extends BaseTimezoneProperty
{
    use UserProperty;
    public const NAME = User::FIELD_TIMEZONE;
    public const LABEL = 'Timezone';
    public const DESCRIPTION = 'Timezone code like America/New_York';
    public $name = self::NAME;
    public $table = User::TABLE;
    public $parentClass = User::class;
}
