<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkNameProperty extends BaseNameProperty
{
	public $canBeChangedToNull = true;
	public $dbInput = 'string,255:nullable';
	public $dbType = PhpTypes::STRING;
	public $default = 'undefined';
	public $description = 'Name of the link.';
	public $example = 'As Needed Meds';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LINK;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::LINK;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'link_name';
            	public $order = '99';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $showOnDetail = true;
	public $title = 'Link Name';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:255';

}
