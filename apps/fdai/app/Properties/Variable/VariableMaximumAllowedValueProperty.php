<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\BaseModel;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseMaximumAllowedValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
class VariableMaximumAllowedValueProperty extends BaseMaximumAllowedValueProperty
{
    use VariableProperty, VariableValueTrait;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    public const SYNONYMS = [
        'common_maximum_allowed_value',
        'common_maximum_allowed_value_in_common_unit',
        'maximum_allowed_value_in_common_unit',
    ];
}
