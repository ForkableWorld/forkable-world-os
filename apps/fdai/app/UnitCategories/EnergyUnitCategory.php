<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UnitCategories;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class EnergyUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = true;
	public const COMBINATION_OPERATION = 'SUM';
	public const FILLING_VALUE = null;
	public const ID = 7;
	public const NAME = 'Energy';
	public const STANDARD_UNIT_ABBREVIATED_NAME = 'cal';
	public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
    public $image = ImageUrls::FITNESS_ENERGY_DRINK;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
    public $fontAwesome = FontAwesome::UTENSIL_SPOON_SOLID;
}
