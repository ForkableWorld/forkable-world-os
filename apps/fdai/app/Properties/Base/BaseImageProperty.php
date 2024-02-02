<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseImageProperty extends BaseImageUrlProperty{
	public $dbInput = 'string,2083';
	public $dbType = PhpTypes::STRING;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'URL to the image of the connector logo';
	public $example = 'https://i.imgur.com/2aUrwtd.png';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::IMAGE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AVATAR_IMAGE;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 2083;
	public $name = self::NAME;
	public const NAME = 'image';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:2083';
	public $title = 'Image';
	public $type = PhpTypes::STRING;
	public $validations = 'required';

}
