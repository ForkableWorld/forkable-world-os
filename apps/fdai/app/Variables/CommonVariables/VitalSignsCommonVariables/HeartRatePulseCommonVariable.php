<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\VitalSignsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Units\BeatsPerMinuteUnit;
class HeartRatePulseCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const DEFAULT_UNIT_ID = BeatsPerMinuteUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DEFAULT_VALUE = 70.0;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1342;
	public const IMAGE_URL = ImageUrls::MEDICAL_HEART;
	public const MANUAL_TRACKING = true; // Need these to show up in search if people want to record manually
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 3600;
	public const NAME = 'Heart Rate (Pulse)';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 26.989999999999998;
	public const PRODUCT_URL = null;
	public const PUBLIC = true;
	public const SYNONYMS = ['Heart Rate', 'Pulse'];
	public const VARIABLE_CATEGORY_ID = VitalSignsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
    public $minimumAllowedSecondsBetweenMeasurements = self::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
