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
class NervousnessCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = OneToFiveRatingUnit::ID;
	public const DEFAULT_VALUE = 1.0;
	public const DESCRIPTION = 'Frightened or worried about something that is happening or might happen.';
	public const ID = 1388;
	public const IMAGE_URL = ImageUrls::EMOTICON_SET_SCARED_2;
	public const ION_ICON = 'ion-sad-outline';
	public const NAME = 'Nervousness';
	public const PRICE = 14.83;
	public const PRODUCT_URL = 'https://www.amazon.com/Dr-Kings-Natural-Medicine-Nervousness/dp/B00111FL3I?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B00111FL3I';
	public const SYNONYMS = ['Nervousness', 'Nervousnes', 'Nervousne'];
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
