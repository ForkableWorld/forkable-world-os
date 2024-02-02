<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Traits\PropertyTraits\VariableProperty;
use App\Properties\Base\BasePriceProperty;
use App\Variables\QMCommonVariable;

class VariablePriceProperty extends BasePriceProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;

    /**
     * @param array $providedParams
     * @param array $newVariable
     * @return array
     */
    public static function setPriceInNewVariableArray(array $providedParams, array $newVariable): array
    {
        if (isset($providedParams['price'])) {
            $newVariable[self::NAME] = $providedParams['price'];
        }
        if (isset($providedParams['ItemAttributes']['ListPrice']['Amount'])) {
            $newVariable[self::NAME] = (int)$providedParams['ItemAttributes']['ListPrice']['Amount'] / 100;
        }
        return $newVariable;
    }
}
