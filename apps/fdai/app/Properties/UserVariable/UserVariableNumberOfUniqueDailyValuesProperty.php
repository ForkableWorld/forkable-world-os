<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseNumberOfUniqueDailyValuesProperty;
class UserVariableNumberOfUniqueDailyValuesProperty extends BaseNumberOfUniqueDailyValuesProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function cannotBeChangedToNull(): bool{
        if($this->getUserVariable()->number_of_processed_daily_measurements !== null){
            return true;
        }
        return parent::cannotBeChangedToNull();
    }
}
