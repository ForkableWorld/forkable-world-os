<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkUrlProperty extends BaseHomepageUrlProperty
{
	public $dbInput = 'string,760';
	public $dbType = PhpTypes::STRING;
	public $default = 'undefined';
	public $description = 'Unique universal resource locator for the link.';
	public $example = 'https://web.quantimo.do/#/app//as-needed-meds';
	public $fieldType = PhpTypes::STRING;
	public $fontAwesome = FontAwesome::LINK;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::LINK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 255;
	public $name = self::NAME;
	public const NAME = 'link_url';
            	public $order = '99';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|max:255';
	public $showOnDetail = true;
	public $title = 'Link Url';
	public $type = PhpTypes::STRING;
	public $validations = 'nullable|max:255';

}
