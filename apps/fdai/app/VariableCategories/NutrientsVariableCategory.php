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
class NutrientsVariableCategory extends QMVariableCategory {
	public const AMAZON_PRODUCT_CATEGORY = null;
	public const BORING = null;
	public const CAUSE_ONLY = true;
	public const COMBINATION_OPERATION = 'MEAN';
	public const COMMON = null;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = 'g';
	public const DEFAULT_UNIT_ID = 6;
	public const DURATION_OF_ACTION = 86400;
	public const EFFECT_ONLY = null;
    public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
    public const FILLING_VALUE = -1;
	public const HELP_TEXT = 'What nutrient do you want to track?';
	public const FONT_AWESOME = FontAwesome::NUTRITIONIX;
	public const ID = 11;
    public const IMAGE_URL = 'https://static.quantimo.do/img/variable_categories/vegetarian_food-96.png';
	public const ION_ICON = 'ion-fork';
	public const MANUAL_TRACKING = false;
	public const MAXIMUM_ALLOWED_VALUE = null;
	public const MEASUREMENT_SYNONYM_SINGULAR_LOWERCASE = 'nutrient';
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const MINIMUM_ALLOWED_VALUE = null;
	public const MORE_INFO = "Nutrients are compounds in foods essential to life and health, providing us with energy, the building blocks for repair and growth and substances necessary to regulate chemical processes.";
	public const NAME = 'Nutrients';
	public const ONSET_DELAY = 0;
	public const OUTCOME = false;
	public const PNG_PATH = 'img/variable_categories/nutrients.png';
    public const PNG_URL = 'https://static.quantimo.do/img/variable_categories/nutrients.png';
	public const PREDICTOR = true;
	public const PUBLIC = true;
	public const SETUP_QUESTION = null;
	public const STUDY_IMAGE_FILE_NAME = null;
	public const SUFFIX = VariableCategoryNameProperty::SUFFIX_INTAKE;
	public const SVG_PATH = 'img/variable_categories/nutrients.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/nutrients.svg';
	public const SYNONYMS = ['Nutrients', 'Nutrient'];
	public const VALENCE = null;
	public const VARIABLE_CATEGORY_NAME = 'Nutrients';
	public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Nutrient';
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
