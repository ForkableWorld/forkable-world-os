<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\UnitCategory;
use App\Models\UnitCategory;
use App\Slim\Model\QMUnitCategory;
use App\Traits\PropertyTraits\UnitCategoryProperty;
use App\Properties\Base\BaseNameProperty;
use App\Types\QMStr;
class UnitCategoryNameProperty extends BaseNameProperty
{
    use UnitCategoryProperty;
    public $table = UnitCategory::TABLE;
    public $parentClass = UnitCategory::class;
	/**
	 * @param string $variableName
	 * @return string
	 */
	public static function fromString(string $variableName): ?string{
		// Regular Can Coke 355ml (12 Oz),  Acidic Foods - 6-oz Granules: Guaranteed,  Jarrow Formulas Curcumin 95, Provides Antioxidant Support, 500 mg, 120 Veggie Caps
		$categories = QMUnitCategory::getIndexedByName();
		foreach($categories as $name => $category){
			if(stripos($variableName, $name) !== false){
				return $name;
			}
		}
		return null;
	}
}
