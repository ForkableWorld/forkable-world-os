<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableCategories;
use App\Buttons\QMButton;
use App\Models\CtCondition;
use App\Models\Variable;
use App\UI\FontAwesome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Variables\QMVariableCategory;
class ConditionsVariableCategory extends QMVariableCategory {
	public const AMAZON_PRODUCT_CATEGORY = null;
	public const BORING = null;
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const COMMON = null;
	public const DEFAULT_UNIT_ABBREVIATED_NAME = null;
	public const DEFAULT_UNIT_ID = null;
	public const DURATION_OF_ACTION = 86400;
	public const EFFECT_ONLY = null;
	public const FILLING_TYPE = null;
	public const FILLING_VALUE = -1;
	public const FONT_AWESOME = FontAwesome::COUCH_SOLID;
	public const ID = 16;
    public const IMAGE_URL = 'https://static.quantimo.do/img/variable_categories/dizzy_person_2-96.png';
	public const ION_ICON = 'ion-ios-medkit';
	public const MANUAL_TRACKING = true;
	public const MAXIMUM_ALLOWED_VALUE = null;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const MINIMUM_ALLOWED_VALUE = null;
	public const MORE_INFO = "Disease states or conditions that produce various symptoms. ";
	public const NAME = 'Conditions';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PNG_PATH = 'img/variable_categories/conditions.png';
    public const PNG_URL = 'https://static.quantimo.do/img/variable_categories/conditions.png';
	public const PREDICTOR = false;
	public const PUBLIC = true;
	public const SETUP_QUESTION = null;
	public const STUDY_IMAGE_FILE_NAME = null;
	public const SUFFIX = null;
	public const SVG_PATH = 'img/variable_categories/conditions.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/conditions.svg';
	public const SYNONYMS = ['Conditions', 'Condition'];
	public const VALENCE = null;
	public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Condition';
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
    /**
     * @return Builder|HasMany|\Illuminate\Database\Query\Builder
     */
    public function indexVariablesQB() {
        $ids = CtCondition::pluck(CtCondition::FIELD_VARIABLE_ID);
        $all = $ids->all();
        return Variable::whereIn(Variable::FIELD_ID, $all);
    }
    /**
     * @return QMButton[]
     */
    public function getVariablesOrButtons(): array{
        $buttons = [];
        $conditions = CtCondition::with('variable')
            ->orderByDesc(CtCondition::FIELD_NUMBER_OF_TREATMENTS)
            ->get();
        /** @var CtCondition $condition */
        foreach($conditions as $condition){
            $buttons[] = $condition->getButton();
        }
        return $buttons;
    }
}
