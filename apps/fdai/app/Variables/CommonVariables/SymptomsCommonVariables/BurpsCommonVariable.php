<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\SymptomsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\SymptomsVariableCategory;
use App\Units\CountUnit;
class BurpsCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'SUM';
	public const DEFAULT_UNIT_ID = CountUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DURATION_OF_ACTION = 86400;
	public const FILLING_VALUE = 0.0;
	public const ID = 5967916;
	public const IMAGE_URL = ImageUrls::EMOTICON_SET_ANGRY_2;
	public const MANUAL_TRACKING = true;
	public const NAME = 'Burps';
	public const ONSET_DELAY = 0;
	public const OUTCOME = true;
	public const PRICE = 24.949999999999999;
	public const PRODUCT_URL = 'https://www.amazon.com/Burts-Bees-Baby-Organic-Alphabet/dp/B01HG74QLI?psc=1&linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01HG74QLI';
	public const PUBLIC = true;
	public const SYNONYMS = ['Burps', 'Burp'];
	public const VARIABLE_CATEGORY_ID = SymptomsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public ?float $fillingValue = self::FILLING_VALUE;
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
