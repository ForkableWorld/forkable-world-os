<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\TreatmentsCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Units\ApplicationsUnit;
class MouthGaurdCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'SUM';
	public const COMMON_ALIAS = null;
	public const DEFAULT_UNIT_ID = ApplicationsUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DESCRIPTION = null;
	public const DURATION_OF_ACTION = 1814400;
	public const FILLING_TYPE = null;
	public const FILLING_VALUE = 0.0;
	public const ID = 84961;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51bctNt%2ByYL._SL160_.jpg';
	public const INFORMATIONAL_URL = null;
	public const MANUAL_TRACKING = true;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const NAME = 'Mouth Gaurd';
	public const ONSET_DELAY = 1800;
	public const OUTCOME = false;
	public const PRICE = 16.899999999999999;
	public const PRODUCT_URL = 'https://www.amazon.com/DentalCare-Labs-Grinding-BPA-Free-Mouldable/dp/B01JNH3XFO?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01JNH3XFO';
	public const PUBLIC = false;
	public const SYNONYMS = [];
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
