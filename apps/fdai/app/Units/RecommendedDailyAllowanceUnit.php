<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Units;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Slim\Model\QMUnit;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class RecommendedDailyAllowanceUnit extends QMUnit {
	public const ABBREVIATED_NAME = '%RDA';
	public const ADVANCED = 1;
	public const CATEGORY_ID = 6;
	public const CATEGORY_NAME = 'Miscellany';
	public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_SUM; // Combination operation refers to how they're combined in a given day, so RDA should be summed over the day, not averaged.  Multi-day aggregation should use the average.
	public const CONVERSION_STEPS = [];
	public const DEFAULT_VALUE = null;
	public const FILLING_VALUE = -1;
	public const HINT = null;
	public const ID = 29;
	public const MANUAL_TRACKING = 0;
	public const MAXIMUM_VALUE = null;
	public $maximumDailyValue = 10000;
	public const MINIMUM_VALUE = 0;
	public const NAME = '% Recommended Daily Allowance';
	public const SCALE = 'ratio';
	public const SUFFIX = 'intake';
	public const SYNONYMS = ['% Recommended Daily Allowance', '%RDA', 'Percent Recommended Daily Allowance'];
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
	public $image = ImageUrls::FITNESS_PROTEIN;
	public $fontAwesome = FontAwesome::UTENSIL_SPOON_SOLID;
}
