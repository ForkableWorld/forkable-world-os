<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableCategories;
use App\Properties\Base\BaseFillingTypeProperty;
use App\UI\FontAwesome;
use App\Variables\QMVariableCategory;
class SymptomsVariableCategory extends QMVariableCategory {
	public const AMAZON_PRODUCT_CATEGORY = null;
	public const BORING = null;
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const COMMON = true;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = '/5';
	public const DEFAULT_UNIT_ID = 10;
	public const DURATION_OF_ACTION = 86400;
	public const EFFECT_ONLY = null;
    public const FEATURE = "Discover the factors most likely to exacerbate or improve severity.";
    public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
    public const FILLING_VALUE = -1;
	public const HELP_TEXT = 'What symptom do you want to record?';
	public const FONT_AWESOME = FontAwesome::HEAD_SIDE_COUGH_SOLID;
	public const ID = 10;
    public const IMAGE_URL = 'https://static.quantimo.do/img/variable_categories/sad-96.png';
	public const ION_ICON = 'ion-sad-outline';
	public const MANUAL_TRACKING = true;
	public const MAXIMUM_ALLOWED_VALUE = null;
	public const MEASUREMENT_SYNONYM_SINGULAR_LOWERCASE = 'rating';
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 60;
	public const MINIMUM_ALLOWED_VALUE = null;
	public const MORE_INFO = 'Symptom severity can be influence by hundreds of factors in daily life. The human mind can only hold 7 numbers in working memory at a time.  I can hold a billion in my mind! If you regularly record your symptoms, add them so I can use this data to determine which hidden and imperceptible factors might be worsening or improving them.';
	public const NAME = 'Symptoms';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PNG_PATH = 'img/variable_categories/symptoms.png';
    public const PNG_URL = 'https://static.quantimo.do/img/variable_categories/symptoms.png';
	public const PREDICTOR = true;
	public const PUBLIC = true;
	public const SETUP_QUESTION = 'What is a negative health symptom you are experiencing?';
	public const STUDY_IMAGE_FILE_NAME = null;
	public const SUFFIX = null;
	public const SVG_PATH = 'img/variable_categories/symptoms.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/symptoms.svg';
	public const SYNONYMS = ['Symptoms', 'Symptom'];
	public const VALENCE = 'negative';
	public const VARIABLE_CATEGORY_NAME = 'Symptoms';
	public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Symptom';
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
