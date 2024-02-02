<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Units;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Slim\Model\QMUnit;
class DegreesFahrenheitUnit extends QMUnit {
	public const ABBREVIATED_NAME = 'F';
	public const ADVANCED = 1;
	public const CATEGORY_ID = 11;
	public const CATEGORY_NAME = 'Temperature';
	public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_MEAN;
	public const CONVERSION_STEPS = [
		['operation' => 'ADD', 'value' => -32],
		['operation' => 'MULTIPLY', 'value' => 0.55555555555556002],
	];
	public const DEFAULT_VALUE = null;
	public const FILLING_VALUE = null;
	public $fillingType = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const HINT = null;
	public const ID = 45;
	public const MANUAL_TRACKING = 1;
	public const MAXIMUM_VALUE = 214;
	public const MINIMUM_VALUE = -87;
	public const NAME = 'Degrees Fahrenheit';
	public const SCALE = 'interval';
	public const SUFFIX = null;
	public const SYNONYMS = ['Degrees Fahrenheit', 'F', "\xc2\xb0F"];
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
