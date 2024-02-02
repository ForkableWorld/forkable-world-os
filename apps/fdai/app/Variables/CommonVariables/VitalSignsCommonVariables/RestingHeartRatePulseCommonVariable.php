<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Variables\CommonVariables\VitalSignsCommonVariables;
use App\UI\ImageUrls;
use App\Variables\QMCommonVariable;
use App\VariableCategories\VitalSignsVariableCategory;
use App\Units\BeatsPerMinuteUnit;
class RestingHeartRatePulseCommonVariable extends QMCommonVariable {
	public const DEFAULT_UNIT_ID = BeatsPerMinuteUnit::ID;
	public const DEFAULT_VALUE = 68.0;
	public const ID = 5211891;
	public const IMAGE_URL = ImageUrls::MEDICAL_HEART;
	public const MANUAL_TRACKING = true; // Need these to show up in search if people want to record manually
	public const NAME = 'Resting Heart Rate (Pulse)';
	public const PRICE = 99.989999999999995;
	public const PRODUCT_URL = 'https://www.amazon.com/Backpod-Treatment-Smartphones-Computers-Costochondritis/dp/B01LYNZBV3?linkCode=xm2&camp=2025&creative=165953&creativeASIN=B01L';
	public const PUBLIC = true;
	public const SYNONYMS = ['Resting Heart Rate', 'Pulse'];
	public const VARIABLE_CATEGORY_ID = VitalSignsVariableCategory::ID;
	public $defaultUnitId = self::DEFAULT_UNIT_ID;
	public $defaultValue = self::DEFAULT_VALUE;
	public $id = self::ID;
	public $imageUrl = self::IMAGE_URL;
	public $manualTracking = self::MANUAL_TRACKING;
	public $name = self::NAME;
	public float $price = self::PRICE;
	public $productUrl = self::PRODUCT_URL;
	public $public = self::PUBLIC;
	public $synonyms = self::SYNONYMS;
	public $variableCategoryId = self::VARIABLE_CATEGORY_ID;
}
