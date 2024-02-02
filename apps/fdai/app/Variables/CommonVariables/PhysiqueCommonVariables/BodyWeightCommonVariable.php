<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\PhysiqueCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\PhysiqueVariableCategory;
use App\Units\PoundsUnit;
class BodyWeightCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = PoundsUnit::ID;
	public const DEFAULT_VALUE = null;
	public const ID = 1486;
	public const IMAGE_URL = ImageUrls::FITNESS_WEIGHING_SCALE;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = null;
	public const NAME = 'Body Weight';
	public const PRICE = 79.989999999999995;
	public const PRODUCT_URL = 'https://www.amazon.com/Digital-Bathroom-Accuracy-Precision-Measurements/dp/B01929N69G?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01929N69G';
	public const PUBLIC = true;
	public const SYNONYMS = ['Body', 'Body Weight', 'Weight'];
	public const VARIABLE_CATEGORY_ID = PhysiqueVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $minimumAllowedSecondsBetweenMeasurements = self::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
