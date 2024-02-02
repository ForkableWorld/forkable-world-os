<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\VariableValueTraits\DailyVariableValueTrait;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseUserMaximumAllowedDailyValueProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
use App\Traits\VariableValueTraits\UserVariableValuePropertyTrait;
class UserVariableUserMaximumAllowedDailyValueProperty extends BaseUserMaximumAllowedDailyValueProperty
{
    use UserVariableProperty, DailyVariableValueTrait, UserHyperParameterTrait, UserVariableValuePropertyTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
}
