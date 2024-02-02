<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Units;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Slim\Model\QMUnit;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class DosesUnit extends QMUnit {
	public const ABBREVIATED_NAME = 'dose';
	public const ADVANCED = 1;
	public const CATEGORY_ID = 13;
	public const CATEGORY_NAME = 'Count';
	public $fillingType = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const COMBINATION_OPERATION = null;
	public const CONVERSION_STEPS = [
		[
			'operation' => 'MULTIPLY',
			'value' => 1,
		],
	];
	public const DEFAULT_VALUE = null;
	public const FILLING_VALUE = 0;
	public const HINT = null;
	public const ID = 210;
	public const MANUAL_TRACKING = 1;
	public const MAXIMUM_VALUE = null;
	public const MINIMUM_VALUE = 0;
	public const NAME = 'Doses';
	public const SCALE = 'ratio';
	public const SUFFIX = null;
	public const SYNONYMS = ['Doses', 'dose', 'Dose'];
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
	public $image = ImageUrls::MEDICAL_PILL;
	public $fontAwesome = FontAwesome::MEDKIT_SOLID;
}
