<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
class UserVariableFillingTypeProperty extends BaseFillingTypeProperty
{
    use UserVariableProperty, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function pluckAndSetDBValue($data){
        return parent::pluckAndSetDBValue($data);
    }
    public static function pluck($data){
        if($type = parent::pluck($data)){
            return $type;
        }
        $val = UserVariableFillingValueProperty::pluck($data);
        if($val === null){
            return null;
        }
        $val = (float)$val;
        if($val === (float)-1){
            return null;
        }
        if($val === (float)0){
            return self::FILLING_TYPE_ZERO;
        }
        if($val){
            return self::FILLING_TYPE_VALUE;
        }
        return null;
    }
}
