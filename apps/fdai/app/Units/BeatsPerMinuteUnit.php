<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Units;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Slim\Model\QMUnit;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class BeatsPerMinuteUnit extends QMUnit {
	public const ABBREVIATED_NAME = 'bpm';
	public const ADVANCED = 1;
	public const CATEGORY_ID = 6;
	public const CATEGORY_NAME = 'Miscellany';
	public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_MEAN;
	public const CONVERSION_STEPS = [];
	public const DEFAULT_VALUE = null;
	public const FILLING_VALUE = null;
	public $fillingType = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const HINT = null;
	public const ID = 26;
	public const MANUAL_TRACKING = 0;
	public const MAXIMUM_VALUE = 300;
	public const MINIMUM_VALUE = 20;
	public const NAME = 'Beats per Minute';
	public const SCALE = 'ratio';
	public const SUFFIX = null;
	public const SYNONYMS = ['Beats per Minute', 'bpm'];
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
	public $image = ImageUrls::MEDICAL_HEART;
	public $fontAwesome = FontAwesome::HEART;
}
