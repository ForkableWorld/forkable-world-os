<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\PhysicalActivityCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\PhysicalActivityVariableCategory;
use App\Units\YesNoUnit;
class DidNeckExercisesCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMMON_ALIAS = null;
	public const DEFAULT_UNIT_ID = YesNoUnit::ID;
	public const CREATOR_USER_ID = 0;
	public const DESCRIPTION = null;
	public const DURATION_OF_ACTION = 604800;
	public const FILLING_TYPE = null;
	public const FILLING_VALUE = 0.0;
	public const ID = 5969803;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/41ACRhCTDFL._SL160_.jpg';
	public const INFORMATIONAL_URL = null;
	public const MANUAL_TRACKING = true;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const NAME = 'Did Neck Exercises';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 10.949999999999999;
	public const PRODUCT_URL = 'https://www.amazon.com/Treat-Your-Own-Neck-803-5/dp/0987650416?linkCode=xm2&camp=2025&creative=165953&creativeASIN=0987650416';
	public const PUBLIC = true;
	public const SYNONYMS = ['Neck Exercises', 'Neck Exercise', 'Did Neck Exercises'];
	public const VARIABLE_CATEGORY_ID = PhysicalActivityVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;

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
