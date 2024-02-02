<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BaseCommonMinimumAllowedNonZeroValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
class VariableCommonMinimumAllowedNonZeroValueProperty extends BaseCommonMinimumAllowedNonZeroValueProperty
{
    use VariableProperty, VariableValueTrait;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
}
