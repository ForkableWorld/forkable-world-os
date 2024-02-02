<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UnitCategories;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class DurationUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = true;
	public const COMBINATION_OPERATION = 'SUM';
	public const FILLING_VALUE = 0;
	public const ID = 1;
	public const NAME = 'Duration';
	public const STANDARD_UNIT_ABBREVIATED_NAME = 's';
    public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
    public $image = ImageUrls::BASIC_FLAT_ICONS_CLOCK;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
    public $fontAwesome = FontAwesome::CLOCK;
}
