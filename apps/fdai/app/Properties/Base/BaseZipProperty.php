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
class BaseZipProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'string,10:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'zip';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::ZIP_CODE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::ZIP_CODE;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 10;
	public $name = self::NAME;
	public const NAME = 'zip';
	public $canBeChangedToNull = true;
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:10';
	public $title = 'Zip';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:10';

}
