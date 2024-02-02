<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UserVariable;
use App\Models\UserVariable;
use App\Traits\PropertyTraits\UserVariableProperty;
use App\Properties\Base\BaseParentIdProperty;
use App\Traits\PropertyTraits\UserHyperParameterTrait;
class UserVariableParentIdProperty extends BaseParentIdProperty
{
    use UserVariableProperty, UserHyperParameterTrait;
    public $table = UserVariable::TABLE;
    public $parentClass = UserVariable::class;
    public function showOnIndex(): bool {return false;}
    public function showOnUpdate(): bool {return false;}
    public function showOnCreate(): bool {return false;}
    public function showOnDetail(): bool {return false;}
	public function validateHyperParameter(){
		$value = $this->getRawOriginalValue();
		if($value === null){return;}
		$this->assertAPIRequest();
		$this->assertParameterNotRedundant();
	}
}
