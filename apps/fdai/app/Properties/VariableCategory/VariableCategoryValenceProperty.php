<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\VariableCategory;
use App\Models\VariableCategory;
use App\Properties\Base\BaseValenceProperty;
use App\Traits\PropertyTraits\VariableCategoryProperty;
class VariableCategoryValenceProperty extends BaseValenceProperty
{
    use VariableCategoryProperty;
    public $table = VariableCategory::TABLE;
    public $parentClass = VariableCategory::class;
	public function validate(): void{
		$this->validateEnum();
	}
}
