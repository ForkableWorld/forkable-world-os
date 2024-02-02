<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\AdminProperty;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseDeviceTokenProperty extends BaseProperty{
	use IsString, AdminProperty;
	public $dbInput = 'string,255';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'device_token';
	public $example = '00929d1846b735a8b98a78a24616dc155aa49b55a36993b3c3277008d63f9ffd';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::DEVICE_TOKEN;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DEVICE_TOKEN;
	public $importance = false;
	public $isOrderable = false;
	public $isPrimary = true;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'device_token';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:255|unique:device_tokens,device_token';
	public $title = 'Device Token';
	public $type = PhpTypes::STRING;
	public $validations = 'required|max:255|unique:device_tokens,device_token';

}
