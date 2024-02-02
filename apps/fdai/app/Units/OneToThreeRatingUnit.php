<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Units;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Slim\Model\QMUnit;
use App\UnitCategories\RatingUnitCategory;
class OneToThreeRatingUnit extends QMUnit {
	public const ABBREVIATED_NAME = '/3';
	public const ADVANCED = 1;
	public const CATEGORY_ID = RatingUnitCategory::ID;
	public const CATEGORY_NAME = RatingUnitCategory::NAME;
	public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_MEAN;
	public const CONVERSION_STEPS = [
		[
			'operation' => 'MULTIPLY',
			'value' => 50,
		],
		[
			'operation' => 'ADD',
			'value' => -50,
		],
	];
	public const DEFAULT_VALUE = null;
	public const FILLING_VALUE = null;
	public $fillingType = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const HINT = null;
	public const ID = 216;
	public const MANUAL_TRACKING = 1;
	public const MAXIMUM_VALUE = 3;
	public const MINIMUM_VALUE = 1;
	public const NAME = '1 to 3 Rating';
	public const SCALE = 'ordinal';
	public const SUFFIX = null;
	public const SYNONYMS = ['out of three', 'out of 3', '1 to 3 Rating', '/3'];
	public $abbreviatedName = self::ABBREVIATED_NAME;
	public $advanced = self::ADVANCED;
	public $unitCategoryId = self::CATEGORY_ID;
	public $categoryName = self::CATEGORY_NAME;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $conversionSteps = self::CONVERSION_STEPS;
	public $defaultValue = self::DEFAULT_VALUE;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $hint = self::HINT;
	public $id = self::ID;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumValue = self::MAXIMUM_VALUE;
	public $minimumValue = self::MINIMUM_VALUE;
	public $name = self::NAME;
	public $scale = self::SCALE;
	public $suffix = self::SUFFIX;
	public $synonyms = self::SYNONYMS;
}
