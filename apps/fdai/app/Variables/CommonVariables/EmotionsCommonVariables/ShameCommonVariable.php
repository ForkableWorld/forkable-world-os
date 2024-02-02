<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\EmotionsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\EmotionsVariableCategory;
use App\Units\OneToFiveRatingUnit;
class ShameCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = OneToFiveRatingUnit::ID;
	public const DEFAULT_VALUE = 1.0;
	public const DESCRIPTION = 'A painful feeling of humiliation or distress caused by the consciousness of wrong or foolish behavior.';
	public const ID = 1443;
	public const IMAGE_URL = ImageUrls::EMOTICON_SET_DISSAPOINTMENT;
	public const ION_ICON = 'ion-sad-outline';
	public const NAME = 'Shame';
	public const PRICE = 13.99;
	public const PRODUCT_URL = 'https://www.amazon.com/Shame-Michael-Fassbender/dp/B007FXLZ9K?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B007FXLZ9K';
	public const SYNONYMS = ['Shame'];
	public const VALENCE = 'negative';
	public const VARIABLE_CATEGORY_ID = EmotionsVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $description = self::DESCRIPTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $ionIcon = self::ION_ICON;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $synonyms = self::SYNONYMS;
	public $valence = self::VALENCE;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
