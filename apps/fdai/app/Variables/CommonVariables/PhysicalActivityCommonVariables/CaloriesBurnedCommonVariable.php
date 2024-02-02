<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\PhysicalActivityCommonVariables;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Variables\QMCommonVariable;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\Units\KilocaloriesUnit;
class CaloriesBurnedCommonVariable extends QMCommonVariable {

	public const DEFAULT_UNIT_ID = KilocaloriesUnit::ID;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1280;
	public const IMAGE_URL = 'https://static.quantimo.do/img/variable_categories/weightlifting-96.png';
	public const MANUAL_TRACKING = false;
	public const MAXIMUM_ALLOWED_VALUE = 20000.0;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 86400;
	public const NAME = 'Calories Burned';
	public const PRICE = 28.989999999999998;
	public const PRODUCT_URL = 'https://www.amazon.com/Omron-BP742N-Pressure-Monitor-Standard/dp/B00KPQB2NS?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B00KPQB2NS';
	public const PUBLIC = true;
	public const SYNONYMS = ['Calories Burned'];
	public const VARIABLE_CATEGORY_ID = PhysicalActivityVariableCategory::ID;
    public const OUTCOME = true;

	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $minimumAllowedSecondsBetweenMeasurements = self::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
    public $outcome = self::OUTCOME;
    public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_SUM;
    public $combinationOperation = self::COMBINATION_OPERATION;
}
