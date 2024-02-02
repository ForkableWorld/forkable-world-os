<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Units;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Slim\Model\Measurement\QMMeasurement;
use App\Slim\Model\QMUnit;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class YesNoUnit extends QMUnit {
	public const ABBREVIATED_NAME = 'yes/no';
	public const ADVANCED = 0;
	public const CATEGORY_ID = 13;
	public const CATEGORY_NAME = 'Count';
	public $fillingType = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_SUM;
	public const CONVERSION_STEPS = [
		[
			'operation' => 'MULTIPLY',
			'value' => 1,
		],
	];
	public const DEFAULT_VALUE = null;
	public const FILLING_VALUE = 0;
	public const HINT = null;
	public const ID = 19;
	public const MANUAL_TRACKING = 1;
	public const MAXIMUM_VALUE = 1;
	public const MINIMUM_VALUE = 0;
	public const NAME = 'Yes/No';
	public const SCALE = 'ordinal';
	public const SUFFIX = null;
	public const SYNONYMS = ['Yes/No', 'yes/no'];
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
	public $image = ImageUrls::FITNESS_CHECK_LIST;
	public $fontAwesome = FontAwesome::CHECK_CIRCLE;
	public static function toRatingIfNecessary($yesOrNo, QMUnit $toUnit): float{
		$number = self::toNumber($yesOrNo);
		if($toUnit->isRating()){
			return self::toRating($number, $toUnit);
		}
		return $number;
	}
	/**
	 * @param float|string $value
	 * @return float
	 */
	public static function toNumber($value): float{
		if(is_string($value)){
			$lower = strtolower($value);
			if($lower === QMMeasurement::STRING_YES){
				$value = 1;
			}
			if($lower === QMMeasurement::STRING_NO){
				$value = 0;
			}
		}
		// Keep float for consistency
		return (float)$value;
	}
	/**
	 * @param int|string $originalValue
	 * @param $toUnit
	 * @return float
	 */
	public static function toRating($originalValue, QMUnit $toUnit): float{
		$number = self::toNumber($originalValue);
		if($number){
			return $toUnit->maximumValue;
		}
		return $toUnit->minimumValue;
	}
}
