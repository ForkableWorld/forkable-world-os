<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\NutrientsCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\NutrientsVariableCategory;
use App\Units\KilocaloriesUnit;
class CaloriesCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;

	public const DEFAULT_UNIT_ID = KilocaloriesUnit::ID;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1499;
    public const MINIMUM_ALLOWED_VALUE = 0; // I guess this has to be zero to allow for fasting?
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51s1vNqrJIL._SL160_.jpg';
	public const NAME = 'Calories';
	public const PRICE = 9.9900000000000002;
	public const PRODUCT_URL = 'https://www.amazon.com/CalorieKing-Calorie-Carbohydrate-Counter-2018/dp/1930448694?linkCode=xm2&camp=2025&creative=165953&creativeASIN=193044869';
	public const PUBLIC = true;
	public const SYNONYMS = ['Calories', 'Calory'];
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
    public $minimumAllowedValue = self::MINIMUM_ALLOWED_VALUE;
}
