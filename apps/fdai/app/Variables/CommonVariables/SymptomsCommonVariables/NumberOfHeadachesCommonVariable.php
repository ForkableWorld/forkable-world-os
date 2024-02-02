<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\SymptomsCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\SymptomsVariableCategory;
use App\Units\CountUnit;
class NumberOfHeadachesCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'SUM';
	public const COMMON_ALIAS = null;
	public const DEFAULT_UNIT_ID = CountUnit::ID;
	public const CREATOR_USER_ID = 0;
	public const DESCRIPTION = null;
	public const DURATION_OF_ACTION = 86400;
	public const FILLING_TYPE = null;
	public const FILLING_VALUE = 0.0;
	public const ID = 2725081;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/419eFm6kS9L._SL160_.jpg';
	public const INFORMATIONAL_URL = null;
	public const MANUAL_TRACKING = true;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const NAME = 'Number of Headaches';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 15.0;
	public const PRODUCT_URL = 'https://www.amazon.com/Migrastil-Migraine-0-3-ounce-Essential-Aromatherapy/dp/B01BT8VCOY?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01';
	public const PUBLIC = true;
	public const SYNONYMS = ['Headache', 'Headache ()', 'Headache (count)', 'Number of Headaches'];
	public const VARIABLE_CATEGORY_ID = SymptomsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
