<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\UnitCategories;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Slim\Model\QMUnitCategory;
class RatingUnitCategory extends QMUnitCategory {
	public const CAN_BE_SUMMED = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const FILLING_VALUE = null;
	public const ID = 5;
	public const NAME = 'Rating';
	public const STANDARD_UNIT_ABBREVIATED_NAME = '%';
    public $canBeSummed = self::CAN_BE_SUMMED;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
    public $image = ImageUrls::AUDIO_AND_VIDEO_CONTROLS_STAR;
	public $name = self::NAME;
	public $standardUnitAbbreviatedName = self::STANDARD_UNIT_ABBREVIATED_NAME;
    public $fontAwesome = FontAwesome::SMILE;
}
