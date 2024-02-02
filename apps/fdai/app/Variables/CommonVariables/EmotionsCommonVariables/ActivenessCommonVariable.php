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
class ActivenessCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = OneToFiveRatingUnit::ID;
	public const DEFAULT_VALUE = 1.0;
	public const DESCRIPTION = 'Someone who is active moves around a lot or does a lot of things.';
	public const ID = 1252;
	public const IMAGE_URL = ImageUrls::EMOTICON_SET_JOYFUL_2;
	public const NAME = 'Activeness';
	public const PRODUCT_URL = 'https://www.amazon.com/Travel-Adventure-vol-activeness-jeopardize-ebook/dp/B01CNZUSWE?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01CNZ';
	public const SYNONYMS = ['Activeness', 'Activenes', 'Activene'];
	public const VALENCE = 'positive';
	public const VARIABLE_CATEGORY_ID = EmotionsVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $description = self::DESCRIPTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $name = self::NAME;
	public $productUrl = self::PRODUCT_URL;
	public $synonyms = self::SYNONYMS;
	public $valence = self::VALENCE;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
