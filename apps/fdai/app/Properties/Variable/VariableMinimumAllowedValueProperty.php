<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMinimumAllowedValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
class VariableMinimumAllowedValueProperty extends BaseMinimumAllowedValueProperty
{
    use VariableValueTrait, VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public const SYNONYMS = [
        'common_minimum_allowed_value_in_common_unit',
        'minimum_allowed_value_in_common_unit',
        'common_minimum_allowed_value',
        'minimum_allowed_value',
    ];
}
