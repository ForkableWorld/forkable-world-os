<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\SleepCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseValenceProperty;
use App\Variables\QMCommonVariable;
class DurationOfAwakeningsDuringSleepCommonVariable extends QMCommonVariable {
	public const COMBINATION_OPERATION = 'SUM';
	public const COMMON_ALIAS = null;
	public const DEFAULT_UNIT_ID = 2;
	public const DESCRIPTION = null;
	public const DURATION_OF_ACTION = 86400;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const ID = 6054544;
	public const IMAGE_URL = null;
	public const INFORMATIONAL_URL = null;
	public const MANUAL_TRACKING = false;
	public const MAXIMUM_ALLOWED_VALUE = 10080;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 86400;
	public const MINIMUM_ALLOWED_VALUE = 0;
	public const NAME = 'Duration of Awakenings During Sleep';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const SYNONYMS = ['Duration of Awakenings During Sleep'];
	public const VARIABLE_CATEGORY_ID = 6;
    public const VALENCE = BaseValenceProperty::VALENCE_NEGATIVE;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $commonAlias = self::COMMON_ALIAS;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $description = self::DESCRIPTION;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $fillingType = self::FILLING_TYPE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $informationalUrl = self::INFORMATIONAL_URL;
    public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $minimumAllowedSecondsBetweenMeasurements = self::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
	public $minimumAllowedValue = self::MINIMUM_ALLOWED_VALUE;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
    public $valence = self::VALENCE;
}
