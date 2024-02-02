<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\EmotionsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\EmotionsVariableCategory;
use App\Units\OneToFiveRatingUnit;
class AnxietyNervousnessCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const COMMON_ALIAS = 'Anxiety / Nervousness';
	public const DEFAULT_UNIT_ID = OneToFiveRatingUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DEFAULT_VALUE = 3.0;
	public const DURATION_OF_ACTION = 86400;
	public const ID = 5903391;
	public const IMAGE_URL = ImageUrls::EMOTICON_SET_SCARED_1;
	public const ION_ICON = 'ion-sad-outline';
	public const MANUAL_TRACKING = true;
	public const NAME = 'Anxiety';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 17.949999999999999;
	public const PRODUCT_URL = 'https://www.amazon.com/Dare-Anxiety-Stop-Panic-Attacks/dp/0956596258?linkCode=xm2&camp=2025&creative=165953&creativeASIN=0956596258';
	public const PUBLIC = true;
	public const SYNONYMS = [
        'Anxiety / Nervousness',
        'Anxiety',
        'Nervousness',
        'Nervous',
    ];
	public const VALENCE = 'negative';
	public const VARIABLE_CATEGORY_ID = EmotionsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $commonAlias = self::COMMON_ALIAS;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $ionIcon = self::ION_ICON;
	public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $valence = self::VALENCE;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
