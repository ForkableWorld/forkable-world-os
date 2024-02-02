<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\PhysiqueCommonVariables;
use App\Properties\Base\BaseValenceProperty;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\PhysiqueVariableCategory;
use App\Units\IndexUnit;
class BodyMassIndexOrBmiCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = IndexUnit::ID;
    public const DURATION_OF_ACTION = 86400;
	public const ID = 1272;
	public const IMAGE_URL = ImageUrls::FITNESS_WEIGHING_SCALE;
	public const MANUAL_TRACKING = false;
	public const MAXIMUM_ALLOWED_VALUE = 100.0;
	public const MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS = 86400;
	public const NAME = 'Body Mass Index Or BMI';
	public const PRICE = 99.989999999999995;
	public const PRODUCT_URL = 'https://www.amazon.com/INEVIFIT-BODY-ANALYZER-Accurate-Bathroom-Composition/dp/B074ZZYFQS?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B074ZZY';
	public const PUBLIC = true;
	public const SYNONYMS = ['BMI', 'Body Mass Index', 'Body Mass Index Or BMI'];
	public const VARIABLE_CATEGORY_ID = PhysiqueVariableCategory::ID;
    public const VALENCE = BaseValenceProperty::VALENCE_NEUTRAL;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
    public $durationOfAction = self::DURATION_OF_ACTION;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $maximumAllowedValue = self::MAXIMUM_ALLOWED_VALUE;
	public $minimumAllowedSecondsBetweenMeasurements = self::MINIMUM_ALLOWED_SECONDS_BETWEEN_MEASUREMENTS;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
    public $valence = self::VALENCE;
}
