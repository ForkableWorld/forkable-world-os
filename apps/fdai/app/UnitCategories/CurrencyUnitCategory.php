<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UnitCategories;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class CurrencyUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = true;
	public const COMBINATION_OPERATION = 'SUM';
	public const FILLING_VALUE = 0;
	public const ID = 12;
	public const NAME = 'Currency';
	public const STANDARD_UNIT_ABBREVIATED_NAME = '$';
    public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
	public $image = ImageUrls::BASIC_FLAT_ICONS_MONEY;
    public $fontAwesome = FontAwesome::MONEY_BILL_ALT;
}
