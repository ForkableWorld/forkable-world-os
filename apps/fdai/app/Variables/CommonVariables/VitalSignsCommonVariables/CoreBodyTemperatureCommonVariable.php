<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\VitalSignsCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Units\DegreesFahrenheitUnit;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Variables\QMCommonVariable;
class CoreBodyTemperatureCommonVariable extends QMCommonVariable {
	public const COMBINATION_OPERATION = 'MEAN';
	public const COMMON_ALIAS = null;
	public const DEFAULT_UNIT_ID = DegreesFahrenheitUnit::ID;
	public const DESCRIPTION = null;
	public const DURATION_OF_ACTION = 604800;
    public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const ID = 1880;
	public const IMAGE_URL = null;
	public const INFORMATIONAL_URL = null;
	public const MANUAL_TRACKING = true;
	public const MAXIMUM_ALLOWED_VALUE = 115;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 60;
	public const MINIMUM_ALLOWED_VALUE = 90;
	public const NAME = 'Core Body Temperature';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const SYNONYMS = ['Body Temperature'];
	public const VARIABLE_CATEGORY_ID = VitalSignsVariableCategory::ID;
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
}
