<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableCategories;
use App\Properties\Base\BaseFillingTypeProperty;
use App\UI\FontAwesome;
use App\Variables\QMVariableCategory;
class TreatmentsVariableCategory extends QMVariableCategory {
	public const AMAZON_PRODUCT_CATEGORY = 'HealthPersonalCare';
	public const APP_TYPE = 'medication';
	public const BORING = null;
	public const CAUSE_ONLY = true;
	public const COMBINATION_OPERATION = 'SUM';
	public const COMMON = true;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = 'count';
	public const DEFAULT_UNIT_ID = 23;
	public const DEFAULT_VALUE_LABEL = 'Dosage';
	public const DEFAULT_VALUE_PLACEHOLDER_TEXT = 'Enter dose value here...';
	public const DURATION_OF_ACTION = 86400;
	public const EFFECT_ONLY = null;
    public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
    public const FILLING_VALUE = 0;
	public const HELP_TEXT = 'What treatment do you want to record?';
	public const FONT_AWESOME = FontAwesome::BRIEFCASE_MEDICAL_SOLID;
	public const ID = 13;
    public const IMAGE_URL = 'https://static.quantimo.do/img/variable_categories/treatments.png';
	public const ION_ICON = 'ion-ios-medkit-outline';
	public const MANUAL_TRACKING = true;
	public const MAXIMUM_ALLOWED_VALUE = null;
	public const MEASUREMENT_SYNONYM_SINGULAR_LOWERCASE = 'dose';
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 60;
	public const MINIMUM_ALLOWED_VALUE = 0;
	public const MORE_INFO = 'Often the effects of medications and treatments aren\'t intuitively perceptible. That\'s where I come in!  If you regularly record your treatments,  I can analyze the data so we can get a better idea which ones are helping you,which one may be harming you, and which ones are merely a waste of money.';
	public const FEATURE = "See the effectiveness of treatments for various conditions and likelihood of side effects. ";
	public const NAME = 'Treatments';
	public const ONSET_DELAY = 1800;
	public const OUTCOME = false;
	public const PNG_PATH = 'img/variable_categories/treatments.png';
    public const PNG_URL = 'https://static.quantimo.do/img/variable_categories/treatments.png';
	public const PREDICTOR = true;
	public const PUBLIC = true;
	public const SETUP_QUESTION = 'What is a medication, treatment, or supplement that you are taking?';
	public const STUDY_IMAGE_FILE_NAME = null;
	public const SUFFIX = null;
	public const SVG_PATH = 'img/variable_categories/treatments.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/treatments.svg';
	public const SYNONYMS = [
    'Health and Beauty',
    'Health & Beauty',
    'Treatments',
    'Treatment',
    'HealthPersonalCare',
    'Baby Product',
    'Home',
];
	public const VALENCE = null;
	public const VARIABLE_CATEGORY_NAME = 'Treatments';
	public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Treatment';
    public $amazonProductCategory = self::AMAZON_PRODUCT_CATEGORY;
	public $appType = self::APP_TYPE;
	public $boring = self::BORING;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $common = self::COMMON;
	public $defaultUnitAbbreviatedName = self::DEFAULT_UNIT_ABBREVIATED_NAME;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValueLabel = self::DEFAULT_VALUE_LABEL;
	public $defaultValuePlaceholderText = self::DEFAULT_VALUE_PLACEHOLDER_TEXT;
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
