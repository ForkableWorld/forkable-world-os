<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\ActivitiesCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\ActivitiesVariableCategory;
use App\Units\HoursUnit;
class TimeSpentOnSoftwareDevelopmentActivitiesCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'SUM';
	public const DEFAULT_UNIT_ID = HoursUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DEFAULT_VALUE = 0.0;
	public const DURATION_OF_ACTION = 604800;
	public const FILLING_VALUE = 0.0;
	public const ID = 111632;
	public const IMAGE_URL = ImageUrls::COMPUTER;
	public const MANUAL_TRACKING = false;
	public const MAXIMUM_ALLOWED_VALUE = 168.0;
	public const MINIMUM_ALLOWED_VALUE = 0.0;
	public const NAME = 'Time Spent On Software Development Activities';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 44.990000000000002;
	public const PRODUCT_URL = 'https://www.amazon.com/Unity-Development-Hours-Teach-Yourself/dp/0672337517?linkCode=xm2&camp=2025&creative=165953&creativeASIN=0672337517';
	public const PUBLIC = true;
	public const SYNONYMS = [
    'Software Development Hours',
    'Software Development Hour',
    'Software Development',
    'Time Spent On Software Development',
    'Time Spent On Software Development Activities',
];
	public const VARIABLE_CATEGORY_ID = ActivitiesVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $minimumAllowedValue = self::MINIMUM_ALLOWED_VALUE;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
