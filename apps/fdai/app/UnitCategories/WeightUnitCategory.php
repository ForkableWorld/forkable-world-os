<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UnitCategories;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class WeightUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = true;
	public const COMBINATION_OPERATION = 'SUM';
	public const FILLING_VALUE = null;
	public const ID = 3;
	public const NAME = 'Weight';
	public const STANDARD_UNIT_ABBREVIATED_NAME = 'kg';
    public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
    public $image = ImageUrls::FITNESS_WEIGHING_SCALE;
    public $fontAwesome = FontAwesome::BALANCE_SCALE_SOLID;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
}
