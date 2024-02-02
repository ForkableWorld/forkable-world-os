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
use OpenApi\Generator;
class BasePlatformProperty extends EnumProperty{
	public const PLATFORM_ANDROID = "android";
	public const PLATFORM_CHROME = "chrome";
	public const PLATFORM_IOS = "ios";
	public const PLATFORM_WEB = "web";
	public const PLATFORM_blink = "blink";
	public const PLATFORM_edge = "edge";
	public const PLATFORM_firefox = "firefox";
	public const PLATFORM_ie = "ie";
	public const PLATFORM_opera = "opera";
	public const PLATFORM_safari = "safari";
	public $dbInput = 'string,255';
	public $dbType = PhpTypes::STRING;
	public $default = Generator::UNDEFINED;
	public $description = 'platform';
	public $example = 'ios';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::SOURCE_PLATFORM;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::SOURCE_PLATFORM;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'platform';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:255';
	public $title = 'Platform';
	public $type = PhpTypes::STRING;
	public $validations = 'required';
	public $enum = [
		self::PLATFORM_ANDROID,
		self::PLATFORM_blink,
		self::PLATFORM_CHROME,
		self::PLATFORM_edge,
		self::PLATFORM_firefox,
		self::PLATFORM_ie,
		self::PLATFORM_IOS,
		self::PLATFORM_opera,
		self::PLATFORM_safari,
		self::PLATFORM_WEB,
    ];
    protected function isLowerCase():bool{return true;}
	public function getEnumOptions(): array{return $this->enum;}
}
