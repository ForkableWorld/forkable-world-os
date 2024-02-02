<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseBrandNameProperty;
use App\Traits\PropertyTraits\VariableProperty;
class VariableBrandNameProperty extends BaseBrandNameProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;
    /**
     * @param $providedParams
     * @param $newVariable
     * @return array
     */
    public static function setBrandInNewVariableArray(array $providedParams, array $newVariable): array
    {
        if (isset($providedParams['ItemAttributes']['Brand'])) {
            $newVariable[Variable::FIELD_BRAND_NAME] = $providedParams['ItemAttributes']['Brand'];
        }
        return $newVariable;
    }
}
