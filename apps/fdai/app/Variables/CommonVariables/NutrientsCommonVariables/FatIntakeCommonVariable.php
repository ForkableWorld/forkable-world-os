<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\NutrientsCommonVariables;
use App\Units\GramsUnit;
use App\Variables\QMCommonVariable;
use App\VariableCategories\NutrientsVariableCategory;
class FatIntakeCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;
	public const DEFAULT_UNIT_ID = GramsUnit::ID;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1310;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51zUCqX2LJL._SL160_.jpg';
    public const MAXIMUM_ALLOWED_VALUE = 5000;
    public const MINIMUM_ALLOWED_VALUE = 0; // I guess this has to be zero to allow for fasting?
	public const NAME = 'Fat Intake';
	public const PRICE = 0.98999999999999999;
	public const PRODUCT_URL = 'https://www.amazon.com/Into-the-Bod-Pod/dp/B077F2SDVB?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B077F2SDVB';
	public const PUBLIC = true;
	public const SYNONYMS = ['Fat', 'Fat Intake'];
	public const VARIABLE_CATEGORY_ID = NutrientsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public ?float $maximumAllowedDailyValue = self::MAXIMUM_ALLOWED_VALUE;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $minimumAllowedValue = self::MINIMUM_ALLOWED_VALUE;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
    public $public = self::PUBLIC;
    public $synonyms = self::SYNONYMS;
    public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
