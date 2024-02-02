<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\FoodsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\FoodsVariableCategory;
use App\Units\ServingUnit;
class CoffeeTeaCocoaCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const COMBINATION_OPERATION = 'SUM';
	public const DEFAULT_UNIT_ID = ServingUnit::ID;
	public const CREATOR_USER_ID = 0;
	public const DEFAULT_VALUE = 1.0;
	public const DURATION_OF_ACTION = 1209600;
	public const FILLING_VALUE = 0.0;
	public const ID = 5978301;
	public const IMAGE_URL = ImageUrls::FITNESS_ENERGY_DRINK;
	public const MANUAL_TRACKING = true;
	public const NAME = 'Coffee, Tea & Cocoa';
	public const ONSET_DELAY = 1800;
	public const OUTCOME = false;
	public const PRICE = 28.0;
	public const PRODUCT_URL = 'https://www.amazon.com/Cappuccino-Chocolate-Brewers-Variety-Sampler/dp/B01N81ANP9?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01N81ANP9';
	public const PUBLIC = true;
	public const SYNONYMS = ['Coffee', 'Coffee, Tea & Cocoa'];
	public const VARIABLE_CATEGORY_ID = FoodsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $combinationOperation = self::COMBINATION_OPERATION;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $defaultValue = self::DEFAULT_VALUE;
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
