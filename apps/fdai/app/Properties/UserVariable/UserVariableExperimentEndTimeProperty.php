<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseExperimentEndTimeProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
class UserVariableExperimentEndTimeProperty extends BaseExperimentEndTimeProperty
{
    use UserVariableProperty, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function getLatestAt(): string{
        return now_at();
    }
}
