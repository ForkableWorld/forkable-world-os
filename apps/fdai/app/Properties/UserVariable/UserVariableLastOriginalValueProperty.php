<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseLastOriginalValueProperty;
use App\Traits\VariableValueTraits\VariableValueTrait;
use App\Slim\Model\QMUnit;
class UserVariableLastOriginalValueProperty extends BaseLastOriginalValueProperty
{
    use UserVariableProperty, VariableValueTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    /**
     * @return float|null
     */
    public function getMaximum(): ?float{
        $uv = $this->getUserVariable();
        $id = $uv->last_original_unit_id;
        if(!$id){return null;}
        $u = QMUnit::find( $id);
        return $u->maximumValue;
    }
    /**
     * @return float|null
     */
    public function getMinimum(): ?float{
        $uv = $this->getUserVariable();
        $id = $uv->last_original_unit_id;
        if(!$id){return null;}
        $u = QMUnit::find( $id);
        return $u->minimumValue;
    }
    public function validate(): void {
        parent::validate();
    }
}
