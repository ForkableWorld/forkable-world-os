<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\TreatmentsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Units\HoursUnit;
class ExerciseCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'SUM';
	public const COMMON_ALIAS = "Exercise";
	public const DEFAULT_UNIT_ID = HoursUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DESCRIPTION = null;
	public const DURATION_OF_ACTION = 1814400;
	public const FILLING_TYPE = null;
	public const FILLING_VALUE = 0.0;
	public const ID = 5954546;
	public const IMAGE_URL = ImageUrls::FITNESS_RUNNER;
	public const INFORMATIONAL_URL = null;
	public const MANUAL_TRACKING = true;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const NAME = 'Exercise  (h)';
	public const ONSET_DELAY = 1800;
	public const OUTCOME = false;
	public const PRICE = 4.9900000000000002;
	public const PRODUCT_URL = 'https://www.amazon.com/Functional-Silicone-Exercise-Mouthpiece-Abcstore99/dp/B01BI4O5IY?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01B';
	public const PUBLIC = true;
	public const SYNONYMS = ['Exercise', 'Exercise  (h)'];
	public const VARIABLE_CATEGORY_ID = TreatmentsVariableCategory::ID;
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
