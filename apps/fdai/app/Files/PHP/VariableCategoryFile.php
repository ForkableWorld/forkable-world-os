<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Files\PHP;
use App\Models\VariableCategory;
class VariableCategoryFile extends HardCodedQMClass {
	public function getFolder(): string{
		return "app/VariableCategories";
	}
	protected static function getBaseModelClass(): string{
		return VariableCategory::class;
	}
}
