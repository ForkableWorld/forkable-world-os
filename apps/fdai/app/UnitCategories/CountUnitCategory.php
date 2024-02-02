<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\UnitCategories;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class CountUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = true;
	public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_SUM;
	public const FILLING_VALUE = 0;
	public const ID = 13;
	public const NAME = 'Count';
	public const STANDARD_UNIT_ABBREVIATED_NAME = 'count';
    public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
	public $image = ImageUrls::FITNESS_MEASURING_TAPE;
    public $fontAwesome = FontAwesome::CALCULATOR_SOLID;
}
