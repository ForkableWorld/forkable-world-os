<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableCategories;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\Base\BaseValenceProperty;
use App\UI\FontAwesome;
use App\Variables\QMVariableCategory;
class GoalsVariableCategory extends QMVariableCategory {
	public const AMAZON_PRODUCT_CATEGORY = null;
	public const BORING = null;
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const COMMON = null;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = null;
	public const DEFAULT_UNIT_ID = null;
	public const DURATION_OF_ACTION = 86400;
	public const EFFECT_ONLY = null;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const FILLING_VALUE = -1;
	public const FONT_AWESOME = FontAwesome::BULLSEYE_SOLID;
	public const ID = 12;
    public const IMAGE_URL = 'https://static.quantimo.do/img/variable_categories/work-96.png';
	public const ION_ICON = 'ion-laptop';
	public const MANUAL_TRACKING = null;  // If specific goals should be false, we should creat hard-coded variables for them that will override this
	public const MAXIMUM_ALLOWED_VALUE = null;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 60;
	public const MINIMUM_ALLOWED_VALUE = null;
	public const MORE_INFO = "Setting goals gives your life direction, and boosts your motivation and self-confidence. ";
	public const NAME = 'Goals';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PNG_PATH = 'img/variable_categories/work.png';
    public const PNG_URL = 'https://static.quantimo.do/img/variable_categories/work.png';
	public const PREDICTOR = false;
	public const PUBLIC = false;
	public const SETUP_QUESTION = null;
	public const STUDY_IMAGE_FILE_NAME = 'work';
	public const SUFFIX = null;
	public const SVG_PATH = 'img/variable_categories/work.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/work.svg';
	public const SYNONYMS = ['Work', 'Productivity', 'Goals', 'Goal'];
	public const VALENCE = BaseValenceProperty::VALENCE_POSITIVE;
	public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Goal';
    public $amazonProductCategory = self::AMAZON_PRODUCT_CATEGORY;
	public $boring = self::BORING;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $common = self::COMMON;
	public $defaultUnitAbbreviatedName = self::DEFAULT_UNIT_ABBREVIATED_NAME;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $effectOnly = self::EFFECT_ONLY;
	public $fillingType = self::FILLING_TYPE;
	public ?float $fillingValue = self::FILLING_VALUE;
	public $fontAwesome = self::FONT_AWESOME;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $ionIcon = self::ION_ICON;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $minimumAllowedSecondsBetweenMeasurements = self::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
	public $minimumAllowedValue = self::MINIMUM_ALLOWED_VALUE;
	public $moreInfo = self::MORE_INFO;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public $pngPath = self::PNG_PATH;
	public $pngUrl = self::PNG_URL;
	public $predictor = self::PREDICTOR;
	public $public = self::PUBLIC;
	public $setupQuestion = self::SETUP_QUESTION;
	public $studyImageFileName = self::STUDY_IMAGE_FILE_NAME;
	public $suffix = self::SUFFIX;
	public $svgPath = self::SVG_PATH;
	public $svgUrl = self::SVG_URL;
	public $synonyms = self::SYNONYMS;
	public $valence = self::VALENCE;
	public $variableCategoryNameSingular = self::VARIABLE_CATEGORY_NAME_SINGULAR;
}
