<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\FoodsCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Variables\QMCommonVariable;
use App\VariableCategories\FoodsVariableCategory;
use App\Units\MilligramsUnit;
class NiacinCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = MilligramsUnit::ID;
	public const DURATION_OF_ACTION = 1209600;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const ID = 1956;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51JSfoYEqNL._SL160_.jpg';
	public const NAME = 'Niacin';
	public const PRODUCT_URL = 'https://www.amazon.com/Java-Kick-Flavored-Infused-B-Complex/dp/B01LW1S0K8?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01LW1S0K8';
	public const PUBLIC = true;
	public const SYNONYMS = ['Niacin'];
	public const VARIABLE_CATEGORY_ID = FoodsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $fillingType = self::FILLING_TYPE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $name = self::NAME;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
