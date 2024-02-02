<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\EnumProperty;
use App\Types\PhpTypes;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class BaseAppTypeProperty extends EnumProperty{
	public $dbInput = 'string,32:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = self::APP_TYPE_GENERAL;
	public $description = 'App types include diet, mood, medication, general, and custom.';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::HAS_ANDROID_APP;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::HAS_ANDROID_APP;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 32;
	public $name = self::NAME;
	public const NAME = 'app_type';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:32';
	public $title = 'App Type';
	public $type = self::TYPE_ENUM;
	public $validations = 'nullable|max:32';
	public $enum = [
		self::APP_TYPE_GENERAL,
		self::APP_TYPE_CUSTOM,
		self::APP_TYPE_DIET,
		self::APP_TYPE_MOOD,
		self::APP_TYPE_MEDICATION,
    ];
	public const APP_TYPE_GENERAL = 'general';
	public const APP_TYPE_CUSTOM = 'custom';
	public const APP_TYPE_MOOD = 'mood';
	public const APP_TYPE_MEDICATION = 'medication';
	public const APP_TYPE_DIET = 'diet';
    protected function isLowerCase():bool{return true;}
	public function getEnumOptions(): array{return $this->enum;}
}
