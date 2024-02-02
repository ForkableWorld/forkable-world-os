<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseAppStatusProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'text,65535:nullable';
	public $dbType = 'text';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Whether the app is active, inactive, or deleted.';
	public $fieldType = 'text';
	public $fontAwesome = FontAwesome::HAS_ANDROID_APP;
	public $htmlInput = 'textarea';
	public $htmlType = 'textarea';
	public $image = ImageUrls::HAS_ANDROID_APP;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'app_status';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $title = 'App Status';
	public $type = PhpTypes::STRING;

}
