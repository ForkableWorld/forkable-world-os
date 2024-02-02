<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\NutrientsCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\Variables\QMCommonVariable;
use App\VariableCategories\NutrientsVariableCategory;
use App\Units\MicrogramsUnit;
class CaroteneAlphaCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = MicrogramsUnit::ID;
	public const DURATION_OF_ACTION = 604800;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const ID = 1964;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/61IiiqkTgsL._SL160_.jpg';
	public const MANUAL_TRACKING = false;
	public const NAME = 'Carotene Alpha';
	public const PRICE = 0.98999999999999999;
	public const PRODUCT_URL = 'https://www.amazon.com/New-Alpha-Beta-Carotene-Remix/dp/B071D12QG9?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B071D12QG9';
	public const PUBLIC = true;
	public const SYNONYMS = ['Carotene Alpha'];
	public const VARIABLE_CATEGORY_ID = NutrientsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $fillingType = self::FILLING_TYPE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
