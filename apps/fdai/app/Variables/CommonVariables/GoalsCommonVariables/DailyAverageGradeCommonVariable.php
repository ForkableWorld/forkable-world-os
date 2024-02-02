<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\GoalsCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\GoalsVariableCategory;
use App\Units\PercentUnit;
class DailyAverageGradeCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = PercentUnit::ID;
	public const CREATOR_USER_ID = 0;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1;
	public const IMAGE_URL = 'https://web.quantimo.do/img/variable_categories/books-96.png';
	public const MANUAL_TRACKING = true;
	public const MAXIMUM_ALLOWED_VALUE = 200.0;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 86400;
	public const NAME = 'Daily Average Grade';
	public const PUBLIC = true;
	public const SYNONYMS = ['Daily Average Grade'];
	public const VARIABLE_CATEGORY_ID = GoalsVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $minimumAllowedSecondsBetweenMeasurements = self::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
	public $name = self::NAME;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
