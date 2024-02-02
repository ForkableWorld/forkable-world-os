<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\NutrientsCommonVariables;
use App\Variables\QMCommonVariable;
use App\VariableCategories\NutrientsVariableCategory;
use App\Units\GramsUnit;
class FructoseCommonVariable extends QMCommonVariable {
	public const CAUSE_ONLY = false;

	public const DEFAULT_UNIT_ID = GramsUnit::ID;
	public const DURATION_OF_ACTION = 1209600;
	public const ID = 1962;
	public const IMAGE_URL = 'https://images-na.ssl-images-amazon.com/images/I/31eKVt4YRDL._SL160_.jpg';
	public const NAME = 'Fructose';
	public const ONSET_DELAY = 1800;
	public const PRODUCT_URL = 'https://www.amazon.com/Now-Foods-Fructose-Fruit-Sugar/dp/B002EB99DC?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B002EB99DC';
	public const PUBLIC = true;
	public const SYNONYMS = ['Fructose'];
	public const VARIABLE_CATEGORY_ID = NutrientsVariableCategory::ID;
	public $causeOnly = self::CAUSE_ONLY;

	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $name = self::NAME;
	public $onsetDelay = self::ONSET_DELAY;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
