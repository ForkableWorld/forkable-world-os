<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\NutrientsCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\NutrientsVariableCategory;
use App\Units\RecommendedDailyAllowanceUnit;
class SeleniumCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = RecommendedDailyAllowanceUnit::ID;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1953;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51XyblPdAoL._SL160_.jpg';
	public const NAME = 'Selenium';
	public const PRICE = 18.989999999999998;
	public const PRODUCT_URL = 'https://www.amazon.com/NOW-Foods-Selenium-200-VCaps/dp/B00SJKPBNY?psc=1&linkCode=xm2&camp=2025&creative=165953&creativeASIN=B00SJKPBNY';
	public const PUBLIC = true;
	public const SYNONYMS = ['Selenium'];
	public const VARIABLE_CATEGORY_ID = NutrientsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
