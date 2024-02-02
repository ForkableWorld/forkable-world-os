<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableCategories;
use App\Properties\VariableCategory\VariableCategoryFillingTypeProperty;
use App\Units\MinutesUnit;
use App\Properties\VariableCategory\VariableCategoryCombinationOperationProperty;
use App\Models\BaseModel;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use Illuminate\Database\Eloquent\Builder;
use App\Variables\QMVariableCategory;
class ActivitiesVariableCategory extends QMVariableCategory {
	public const NAME_SINGULAR = 'Activity';
	public const WP_POST_ID = null;
	public const UPDATED_AT = '2021-01-27 02:24:48';
	public const NUMBER_OF_VARIABLES = 1637;
	public const NUMBER_OF_USER_VARIABLES = 9345;
	public const NUMBER_OF_PREDICTOR_POPULATION_STUDIES = 6406;
	public const NUMBER_OF_PREDICTOR_CASE_STUDIES = 16384;
	public const NUMBER_OF_OUTCOME_POPULATION_STUDIES = 14294;
	public const NUMBER_OF_OUTCOME_CASE_STUDIES = 46999;
	public const NUMBER_OF_MEASUREMENTS = 1325498;
	public const MEDIAN_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const IS_PUBLIC = false;
	public const DELETED_AT = null;
	public const CREATED_AT = '2020-07-28 17:56:03';
	public const AVERAGE_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const AMAZON_PRODUCT_CATEGORY = null;
	public const BORING = false;
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = VariableCategoryCombinationOperationProperty::COMBINATION_SUM;
	public const COMMON = null;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = 'min';
	public const DEFAULT_UNIT_ID = MinutesUnit::ID;
	public const DURATION_OF_ACTION = 86400;
	public const EFFECT_ONLY = null;
	public const FILLING_TYPE = VariableCategoryFillingTypeProperty::FILLING_TYPE_ZERO;
	public const FILLING_VALUE = 0;
	public const HELP_TEXT = 'What activity do you want to record?';
	public const FONT_AWESOME = FontAwesome::CALENDAR_CHECK;
	public const ID = 14;
	public const IMAGE_URL = ImageUrls::CLOCK;
	public const ION_ICON = 'ion-ios-body-outline';
	public const MANUAL_TRACKING = null; // Leave this null so it can be set at the variable level.  Rescuetime variables false manual in their models
	public const MAXIMUM_ALLOWED_VALUE = null;
	public const MEASUREMENT_SYNONYM_SINGULAR_LOWERCASE = 'activity';
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const MINIMUM_ALLOWED_VALUE = null;
	public const MORE_INFO = 'Non-physical activities like studying.';
	public const NAME = 'Activities';
	public const ONSET_DELAY = 0;
	public const OUTCOME = null; // Leave this null so it can be set at the variable level.  // Keystrokes are activities and it may be a goal for keystrokes to be higher.  Also, maybe people have a goal of engaging (or their kids) in certain activities more
	public const PNG_PATH = 'img/variable_categories/activity.png';
    public const PNG_URL = 'https://static.quantimo.do/img/variable_categories/activity.png';
	public const PREDICTOR = true;
	public const PUBLIC = false;
	public const SETUP_QUESTION = null;
	public const STUDY_IMAGE_FILE_NAME = 'activity';
	public const SUFFIX = null;
	public const SVG_PATH = 'img/variable_categories/activity.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/activity.svg';
	public const SYNONYMS = ['Activities', 'Activity'];
	public const VALENCE = null;
	public const VARIABLE_CATEGORY_NAME = 'Activity';
	public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Activity';
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
	public function __construct($variableCategory = null){
        parent::__construct($variableCategory);
        if($this->manualTracking !== null){
            $this->validateManualTracking($this->manualTracking);
        }
    }
    public function getManualTracking(): ?bool{
        $val = parent::getManualTracking();
        $this->validateManualTracking($val);
        return $val;
    }
    /**
     * @param bool|null $val
     */
    private function validateManualTracking($val): void{
        if($val !== null){
            le("Leave manualTracking null so it can be set at the variable level.  Rescuetime variables false manual in their models");
        }
    }
    public function setAttribute($key, $value){
        le("why are we setting $key");
        return parent::setAttribute($key, $value);
    }
    public function populateByLaravelModel(BaseModel $l){
        parent::populateByLaravelModel($l);
    }
    public function setLaravelModel(BaseModel $laravelModel): BaseModel{
        return parent::setLaravelModel($laravelModel);
    }
    public function indexVariablesQB(): Builder{
        return $this->publicStudyVariablesQB();
    }
}
