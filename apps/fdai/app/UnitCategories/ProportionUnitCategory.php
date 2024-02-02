<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UnitCategories;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class ProportionUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const FILLING_VALUE = null;
	public const ID = 8;
	public const NAME = 'Proportion';
	public const STANDARD_UNIT_ABBREVIATED_NAME = null;
	public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
    public $image = ImageUrls::WORK_PRODUCTIVITY_RATING;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
    public $fontAwesome = FontAwesome::RULER_SOLID;
}
