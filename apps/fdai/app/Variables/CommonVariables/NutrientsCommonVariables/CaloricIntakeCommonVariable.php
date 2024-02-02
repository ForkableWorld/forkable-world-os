<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\NutrientsCommonVariables;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\NutrientsVariableCategory;
use App\Units\KilocaloriesUnit;
class CaloricIntakeCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = KilocaloriesUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1283;
	public const IMAGE_URL = ImageUrls::FITNESS_FAST_FOOD;
	public const MANUAL_TRACKING = false;
	public const MAXIMUM_ALLOWED_VALUE = 35000.0;
	public const NAME = 'Caloric Intake';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 18.949999999999999;
	public const PRODUCT_URL = 'https://www.amazon.com/Steps-Reverse-Your-PCOS-Metabolism/dp/1626343012?linkCode=xm2&camp=2025&creative=165953&creativeASIN=1626343012';
	public const PUBLIC = true;
	public const SYNONYMS = ['CaloriesIn', 'Caloric Intake'];
	public const VARIABLE_CATEGORY_ID = NutrientsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
