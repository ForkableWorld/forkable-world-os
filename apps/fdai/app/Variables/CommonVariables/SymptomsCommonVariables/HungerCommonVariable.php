<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\SymptomsCommonVariables;
use App\VariableCategories\FoodsVariableCategory;
use App\Variables\QMCommonVariable;
use App\VariableCategories\SymptomsVariableCategory;
use App\Units\OneToFiveRatingUnit;
class HungerCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = OneToFiveRatingUnit::ID;
	public const CREATOR_USER_ID = 7;
	public const DESCRIPTION = 'Desire to eat';
	public const ID = 102685;
	public const IMAGE_URL = FoodsVariableCategory::IMAGE_URL;
	public const MANUAL_TRACKING = true;
	public const NAME = 'Hunger';
	public const PRODUCT_URL = false;
	public const PUBLIC = true;
	public const SYNONYMS = ['Hunger', 'Hungriness', 'Hungry', 'Hunger Rating', 'Hungriness Rating'];
	public const VALENCE = 'neutral';
	public const VARIABLE_CATEGORY_ID = SymptomsVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $creatorUserId = self::CREATOR_USER_ID;
	public $description = self::DESCRIPTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $valence = self::VALENCE;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
