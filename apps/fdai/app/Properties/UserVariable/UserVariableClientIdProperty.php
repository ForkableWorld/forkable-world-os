<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseClientIdProperty;
class UserVariableClientIdProperty extends BaseClientIdProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public $canBeChangedToNull = true;
    public $shouldNotContain = [
        // TODO: Switch to API keys instead of client id's in client requests
        // BaseClientIdProperty::CLIENT_ID_QUANTIMODO, // Not sure why moneymodo keeps getting replaced?
    ];
}
