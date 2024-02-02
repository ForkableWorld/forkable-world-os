<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\FoodsCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\FoodsVariableCategory;
use App\Units\ServingUnit;
class WaterCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = ServingUnit::ID;
	public const DURATION_OF_ACTION = 1209600;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
    public const MINIMUM_ALLOWED_VALUE = 1;
	public const ID = 1905;
	public const IMAGE_URL = ImageUrls::FITNESS_WATER;
	public const NAME = 'Water';
	public const PRICE = 3.04;
	public const PRODUCT_URL = 'https://www.amazon.com/Nestle-Pure-Life-Purified-Plastic/dp/B00NP79AI8?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B00NP79AI8';
	public const PUBLIC = true;
	public const SYNONYMS = ['Water'];
	public const VARIABLE_CATEGORY_ID = FoodsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $fillingType = self::FILLING_TYPE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
	public $minimumAllowedValue = self::MINIMUM_ALLOWED_VALUE;
}
