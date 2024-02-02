<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableCategories;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Properties\VariableCategory\VariableCategoryNameProperty;
use App\UI\FontAwesome;
use App\Variables\QMVariableCategory;
class FoodsVariableCategory extends QMVariableCategory {
	public const AMAZON_PRODUCT_CATEGORY = 'GourmetFood';
	public const APP_TYPE = 'diet';
	public const BORING = null;
	public const CAUSE_ONLY = true;
	public const COMBINATION_OPERATION = 'SUM';
	public const COMMON = true;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = 'serving';
	public const DEFAULT_UNIT_ID = 44;
	public const DURATION_OF_ACTION = 864000; // 10 days
	public const EFFECT_ONLY = null;
    public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
    public const FILLING_VALUE = 0;
	public const HELP_TEXT = 'What did you eat?';
	public const FONT_AWESOME = FontAwesome::UTENSILS_SOLID;
	public const ID = 15;
    public const IMAGE_URL = 'https://static.quantimo.do/img/variable_categories/vegetarian_food-96.png';
	public const ION_ICON = 'ion-fork';
	public const MANUAL_TRACKING = true;
	public const MAXIMUM_ALLOWED_VALUE = null;
	public const MEASUREMENT_SYNONYM_SINGULAR_LOWERCASE = 'meal';
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const MINIMUM_ALLOWED_VALUE = 0;
	public const MORE_INFO = 'Diet can have a significant impact on your health. It\'s important to enter any foods that you regularly eat to see how they might be affecting you.';
	public const NAME = 'Foods';
	public const ONSET_DELAY = 1800;
	public const OUTCOME = false;
	public const PNG_PATH = 'img/variable_categories/foods.png';
    public const PNG_URL = 'https://static.quantimo.do/img/variable_categories/foods.png';
	public const PREDICTOR = true;
	public const PUBLIC = true;
	public const SETUP_QUESTION = 'What is a food or drink that you consume more than a few times a week?';
	public const STUDY_IMAGE_FILE_NAME = null;
	public const SUFFIX = VariableCategoryNameProperty::SUFFIX_INTAKE;
	public const SVG_PATH = 'img/variable_categories/foods.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/foods.svg';
	public const SYNONYMS = ['Grocery', 'Foods', 'Food', 'GourmetFood'];
	public const VALENCE = null;
	public const VARIABLE_CATEGORY_NAME = 'Foods';
	public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Food';
    public $amazonProductCategory = self::AMAZON_PRODUCT_CATEGORY;
	public $appType = self::APP_TYPE;
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
	public $helpText = self::HELP_TEXT;
	public $fontAwesome = self::FONT_AWESOME;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $ionIcon = self::ION_ICON;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $measurementSynonymSingularLowercase = self::MEASUREMENT_SYNONYM_SINGULAR_LOWERCASE;
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
	public $variableCategoryName = self::VARIABLE_CATEGORY_NAME;
	public $variableCategoryNameSingular = self::VARIABLE_CATEGORY_NAME_SINGULAR;
}
