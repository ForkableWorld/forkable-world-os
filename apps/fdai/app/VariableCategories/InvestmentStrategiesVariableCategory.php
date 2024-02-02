<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\VariableCategories;
use App\Pages\StrategyComparisonPage;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use Illuminate\View\View;
use App\Units\PercentUnit;
use App\Variables\QMVariableCategory;
class InvestmentStrategiesVariableCategory extends QMVariableCategory
{
    public const AMAZON_PRODUCT_CATEGORY = null;
    public const BORING = true;
    public const CAUSE_ONLY = false;
    public const COMBINATION_OPERATION = BaseCombinationOperationProperty::COMBINATION_MEAN;
    public const COMMON = false;
    public const DEFAULT_UNIT_ABBREVIATED_NAME = PercentUnit::ABBREVIATED_NAME;
    public const DEFAULT_UNIT_ID = PercentUnit::ID;
    public const DURATION_OF_ACTION = 86400;
    public const EFFECT_ONLY = false;
    public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
    public const FILLING_VALUE = -1;
    public const FONT_AWESOME = FontAwesome::MONEY_BILL_ALT;
    public const ID = 255;
    public const IMAGE_URL = ImageUrls::MONEY_MONEY_NOTES;
    public const ION_ICON = IonIcon::money;
    public const MANUAL_TRACKING = false;
    public const MAXIMUM_ALLOWED_VALUE = null;
    public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
    public const MINIMUM_ALLOWED_VALUE = null;
    public const MORE_INFO = "An investment strategy is what guides an investor's decisions based on goals, risk tolerance, and future needs for capital. ";
    public const NAME = 'Investment Strategies';
    public const ONSET_DELAY = 0;
    public const OUTCOME = true;
    public const PNG_PATH = 'img/variable_categories/payments.png';
    public const PNG_URL = ImageUrls::BASIC_FLAT_ICONS_MONEY;
    public const PREDICTOR = true;
    public const PUBLIC = true;
    public const SETUP_QUESTION = null;
    public const STUDY_IMAGE_FILE_NAME = null;
    public const SUFFIX = null;
    public const SVG_PATH = 'img/variable_categories/payments.svg';
    public const SVG_URL = 'https://static.quantimo.do/img/variable_categories/payments.svg';
    public const SYNONYMS = ['Investment Strategy', self::NAME];
    public const VALENCE = null;
    public const VARIABLE_CATEGORY_NAME_SINGULAR = 'Investment Strategy';
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
    public function getShowPageView(array $params = []): View{
        return StrategyComparisonPage::getStrategyComparisonView($params);
    }
    public function getShowContentView(array $params = []): View{
        return StrategyComparisonPage::instance()->getContentView($params);
    }
}
