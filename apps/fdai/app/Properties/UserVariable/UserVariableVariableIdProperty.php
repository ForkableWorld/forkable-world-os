<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Traits\ForeignKeyIdTrait;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseVariableIdProperty;
class UserVariableVariableIdProperty extends BaseVariableIdProperty
{
    use UserVariableProperty, ForeignKeyIdTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @param $data
     * @return Variable
     */
    public static function findRelated($data): Variable{
        $id = static::pluckOrDefault($data);
        return Variable::findInMemoryOrDB($id);
    }
}
