<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\TreatmentsCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Variables\QMCommonVariable;
use App\VariableCategories\TreatmentsVariableCategory;
use App\Units\TabletsUnit;
class SuifamethoxazoleThpDsCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = TabletsUnit::ID;
	public const DEFAULT_VALUE = 15.0;
	public const DURATION_OF_ACTION = 1814400;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const ID = 1453;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51UYcQnbqQL._SL160_.jpg';
	public const NAME = 'SuIfamethoxazole THP DS';
	public const PRICE = 69.989999999999995;
	public const PRODUCT_URL = 'https://www.amazon.com/Cosaminds-Mds108-Cosamin-180-Count/dp/B00005KGUB?psc=1&linkCode=xm2&camp=2025&creative=165953&creativeASIN=B00005KGUB';
	public const PUBLIC = true;
	public const SYNONYMS = ['SuIfamethoxazole THP DS', 'SuIfamethoxazole THP D'];
	public const VARIABLE_CATEGORY_ID = TreatmentsVariableCategory::ID;
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
