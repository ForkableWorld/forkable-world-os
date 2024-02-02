<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Units;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Slim\Model\QMUnit;
class MinutesUnit extends QMUnit {
	public const ABBREVIATED_NAME = 'min';
	public const ADVANCED = 0;
	public const CATEGORY_ID = 1;
	public const CATEGORY_NAME = 'Duration';
	public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_SUM;
	public const CONVERSION_STEPS = [
		[
			'operation' => 'MULTIPLY',
			'value' => 60,
		],
	];
	public const DEFAULT_VALUE = null;
	public const FILLING_VALUE = 0;
	public $fillingType = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const HINT = null;
	public const ID = 2;
	public const MANUAL_TRACKING = 1;
	public const MAXIMUM_VALUE = 10080;
	public $maximumDailyValue = 60 * 24;
	public const MINIMUM_VALUE = 0;
	public const NAME = 'Minutes';
	public const SCALE = 'ratio';
	public const SUFFIX = null;
	public const SYNONYMS = ['Minutes', 'min', 'Minute'];
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
