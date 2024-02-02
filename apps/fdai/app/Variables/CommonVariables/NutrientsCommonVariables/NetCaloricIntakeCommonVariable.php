<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\NutrientsCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\NutrientsVariableCategory;
use App\Units\KilocaloriesUnit;
class NetCaloricIntakeCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;

	public const DEFAULT_UNIT_ID = KilocaloriesUnit::ID;
	public const DURATION_OF_ACTION = 604800;
	public const ID = 1507;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/51SOoaceWOL._SL160_.jpg';
	public const NAME = 'Net Caloric Intake';
	public const PRICE = 11.69;
	public const PRODUCT_URL = 'https://www.amazon.com/Purina-Beneful-Baked-Delights-Snacks/dp/B012HYDSW8?psc=1&linkCode=xm2&camp=2025&creative=165953&creativeASIN=B012HYDSW8';
	public const PUBLIC = true;
	public const SYNONYMS = ['Net Caloric Intake'];
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
