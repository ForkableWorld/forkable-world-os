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
class BaseDowncaseNameProperty extends BaseNameProperty{
	use IsString;
	public $dbInput = 'string,4369:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'downcase_name';
	public $example = 'schizophrenia';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::DISPLAY_NAME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::DISPLAY_NAME;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 4369;
	public $name = self::NAME;
	public const NAME = 'downcase_name';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:4369';
	public $title = 'Downcase Name';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:4369';

}
