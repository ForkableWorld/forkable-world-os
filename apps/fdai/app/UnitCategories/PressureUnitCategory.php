<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UnitCategories;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class PressureUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const FILLING_VALUE = null;
	public const ID = 10;
	public const NAME = 'Pressure';
	public const STANDARD_UNIT_ABBREVIATED_NAME = 'Pa';
	public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
    public $image = ImageUrls::ACTIVITIES_SUN_UMBRELLA;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
    public $fontAwesome = FontAwesome::CLOUD_MOON_SOLID;
}
