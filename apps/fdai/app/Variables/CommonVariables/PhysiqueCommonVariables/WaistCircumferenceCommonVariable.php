<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\PhysiqueCommonVariables;
use App\Properties\Base\BaseFillingTypeProperty;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\PhysiqueVariableCategory;
use App\Units\InchesUnit;
class WaistCircumferenceCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = InchesUnit::ID;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_NONE;
	public const ID = 1511;
	public const IMAGE_URL = ImageUrls::FITNESS_MEASURING_TAPE;
	public const NAME = 'Waist Circumference';
	public const PRICE = 34.990000000000002;
	public const PRODUCT_URL = 'https://www.amazon.com/BraceAbility-Bariatric-Abdominal-Compression-Circumference/dp/B00QLYYRZ8?psc=1&linkCode=xm2&camp=2025&creative=165953&cre';
	public const PUBLIC = true;
	public const SYNONYMS = ['Waist Circumference'];
	public const VARIABLE_CATEGORY_ID = PhysiqueVariableCategory::ID;
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
