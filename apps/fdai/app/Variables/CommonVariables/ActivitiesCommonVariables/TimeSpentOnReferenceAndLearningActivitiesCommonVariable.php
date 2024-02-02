<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\ActivitiesCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\UI\ImageUrls;
use App\Units\HoursUnit;
use App\Variables\QMCommonVariable;
class TimeSpentOnReferenceAndLearningActivitiesCommonVariable extends QMCommonVariable {
	public const COMBINATION_OPERATION = 'SUM';
	public const COMMON_ALIAS = null;
	public const DEFAULT_UNIT_ID = HoursUnit::ID;
	public const DESCRIPTION = null;
	public const DURATION_OF_ACTION = 604800;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const ID = 111642;
	public const IMAGE_URL = ImageUrls::ACTIVITIES_OPEN_BOOK;
	public const INFORMATIONAL_URL = null;
	public const MANUAL_TRACKING = false;
	public const MAXIMUM_ALLOWED_VALUE = 168;
	public const MINIMUM_ALLOWED_VALUE = 0;
	public const NAME = 'Time Spent On Reference And Learning Activities';
	public const ONSET_DELAY = 0;
	public const OUTCOME = null; // Leave null so the user can decide
	public const SYNONYMS = [
    'Reference And Learning Hours',
    'Reference And Learning Hour',
    'Reference And Learning',
    'Time Spent On Reference And Learning',
    'Time Spent On Reference And Learning Activities'
];
	public const VARIABLE_CATEGORY_ID = 14;
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
	public $minimumAllowedValue = self::MINIMUM_ALLOWED_VALUE;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
