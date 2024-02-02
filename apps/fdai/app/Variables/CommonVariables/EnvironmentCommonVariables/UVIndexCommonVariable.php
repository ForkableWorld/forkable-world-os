<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\EnvironmentCommonVariables;
use App\Properties\Base\BaseCombinationOperationProperty;
use App\Properties\Base\BaseFillingTypeProperty;
use App\UI\ImageUrls;
use App\VariableCategories\EnvironmentVariableCategory;
use App\Variables\QMCommonVariable;
use App\Units\IndexUnit;
class UVIndexCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = IndexUnit::ID;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const ID = 5974181;
	public const IMAGE_URL = ImageUrls::AGRICULTURE_SUN;
	public const NAME = 'UV Index';
	public const PRICE = 74.989999999999995;
	public const PRODUCT_URL = 'https://www.amazon.com/ELEOPTION-Precision-Strength-Photometer-Detector/dp/B0722WKP73?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B0722W';
	public const PUBLIC = true;
	public const SYNONYMS = ['UV Index'];
	public const VARIABLE_CATEGORY_ID = EnvironmentVariableCategory::ID;

	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $fillingType = self::FILLING_TYPE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
