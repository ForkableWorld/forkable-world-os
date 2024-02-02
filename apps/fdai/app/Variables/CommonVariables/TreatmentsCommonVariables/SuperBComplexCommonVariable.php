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
class SuperBComplexCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = TabletsUnit::ID;
	public const DURATION_OF_ACTION = 1814400;
	public const FILLING_TYPE = BaseFillingTypeProperty::FILLING_TYPE_ZERO;
	public const ID = 1454;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/41Tf34r0gTL._SL160_.jpg';
	public const NAME = 'Super B Complex';
	public const PRICE = 17.489999999999998;
	public const PRODUCT_URL = 'https://www.amazon.com/RECOMMENDED-HEALTHLINE-PROBIOTICS-EFFECTIVE-CONSTIPATION/dp/B00WTGTTXW?linkCode=xm2&camp=2025&creative=165953&creativeASI';
	public const PUBLIC = true;
	public const SYNONYMS = ['Super B Complex'];
	public const VARIABLE_CATEGORY_ID = TreatmentsVariableCategory::ID;
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
}
