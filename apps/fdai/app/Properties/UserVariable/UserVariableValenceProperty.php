<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseValenceProperty;
use LogicException;
use App\Variables\CommonVariables\SymptomsCommonVariables\VividDreamsCommonVariable;
use App\Variables\QMUserVariable;
class UserVariableValenceProperty extends BaseValenceProperty
{
    use UserVariableProperty;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function validate(): void {
        if(!$this->shouldValidate()){return;}
        $uv = $this->getUserVariable();
        $unit = $uv->getQMUnit();
        parent::validate();
        $value = $this->getDBValue();
        if($uv->variable_id === VividDreamsCommonVariable::ID && $value !== BaseValenceProperty::VALENCE_NEUTRAL){
            le("Why are we setting vivid dreams valence to $value");
        }
    }
}
