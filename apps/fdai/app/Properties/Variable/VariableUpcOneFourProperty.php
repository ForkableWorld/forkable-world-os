<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Variable;
use App\Models\Variable;
use App\Properties\Base\BaseUpcOneFourProperty;
use App\Traits\PropertyTraits\VariableProperty;
class VariableUpcOneFourProperty extends BaseUpcOneFourProperty
{
    use VariableProperty;
    public $table = Variable::TABLE;
    public $parentClass = Variable::class;

    /**
     * @param array $newVariableData
     * @param array $newVariable
     * @return array
     */
    public static function setUpcInNewVariableArray($newVariableData, $newVariable)
    {
        if (isset($newVariableData['upc_12'])) {
            $newVariable[Variable::FIELD_UPC_14] = $newVariableData['upc_12'];
        }
        if (isset($newVariableData['upc_14'])) {
            $newVariable[Variable::FIELD_UPC_14] = $newVariableData['upc_14'];
        }
        if (isset($newVariableData['upc'])) {
            $newVariable[Variable::FIELD_UPC_14] = $newVariableData['upc'];
        }
        if (isset($newVariableData['ItemAttributes']['UPC'])) {
            $newVariable[Variable::FIELD_UPC_14] = $newVariableData['ItemAttributes']['UPC'];
        }
        return $newVariable;
    }
}
