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
class BaseBrandNameProperty extends BaseNameProperty{
	use IsString;
	public $dbInput = 'string,125:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'brand_name';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::CARD_BRAND;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::CARD_BRAND;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 125;
	public $name = self::NAME;
	public const NAME = 'brand_name';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:125';
	public $title = 'Brand Name';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|max:125';

}
