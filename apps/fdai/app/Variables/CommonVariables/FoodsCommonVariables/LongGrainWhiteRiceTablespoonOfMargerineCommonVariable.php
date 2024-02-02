<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\FoodsCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Variables\QMCommonVariable;
use App\VariableCategories\FoodsVariableCategory;
use App\Units\GramsUnit;
class LongGrainWhiteRiceTablespoonOfMargerineCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = GramsUnit::ID;
	public const DEFAULT_VALUE = 1.0;
	public const DURATION_OF_ACTION = 1209600;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const ID = 1975;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51SXYqacRSL._SL160_.jpg';
	public const NAME = 'Long Grain White Rice + Tablespoon of Margerine';
	public const PRICE = 21.390000000000001;
	public const PRODUCT_URL = 'https://www.amazon.com/Gallon-Liquid-Butter-Alternative-grill/dp/B01JN1N8YG?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01JN1N8YG';
	public const PUBLIC = true;
	public const SYNONYMS = ['Long Grain White Rice + Tablespoon of Margerine'];
	public const VARIABLE_CATEGORY_ID = FoodsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValue = self::DEFAULT_VALUE;
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
}
