<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\SymptomsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\SymptomsVariableCategory;
use App\Units\OneToFiveRatingUnit;
class NeckPainCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'MEAN';
	public const DEFAULT_UNIT_ID = OneToFiveRatingUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DEFAULT_VALUE = 4.0;
	public const DURATION_OF_ACTION = 86400;
	public const ID = 84887;
	public const IMAGE_URL = ImageUrls::EMOTICON_SET_CREEPY;
	public const MANUAL_TRACKING = true;
	public const NAME = 'Neck Pain';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 89.989999999999995;
	public const PRODUCT_URL = 'https://www.amazon.com/Backpod-Treatment-Smartphones-Computers-Costochondritis/dp/B01LYNZBV3?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01L';
	public const PUBLIC = true;
	public const SYNONYMS = ['Neck Pain'];
	public const VARIABLE_CATEGORY_ID = SymptomsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $outcome = self::OUTCOME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
