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
class BaseAppDisplayNameProperty extends BaseNameProperty {
	use IsString;
	public $dbInput = 'string,255';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The display name of the app.';
	public $example = 'Admin\'s App';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::DISPLAY_NAME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DISPLAY_NAME;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'app_display_name';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:255';
	public $title = 'App Display Name';
	public $type = PhpTypes::STRING;
	public $validations = 'required';

}
