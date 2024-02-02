<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkImageProperty extends BaseImageProperty
{
	public $canBeChangedToNull = true;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = 'undefined';
	public $description = 'URL of an image related to the link.';
	public $example = 'https://static.quantimo.do/img/Ionicons/png/512/star.png';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::AVATAR_IMAGE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::AVATAR_IMAGE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'link_image';
            	public $order = '99';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $showOnDetail = true;
	public $title = 'Link Image';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:255';

}
