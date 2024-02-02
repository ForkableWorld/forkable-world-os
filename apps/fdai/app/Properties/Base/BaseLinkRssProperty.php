<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkRssProperty extends BaseLinkUrlProperty
{
	public $dbInput = 'string,255';
	public $dbType = PhpTypes::STRING;
	public $default = 'undefined';
	public $description = 'link_rss';
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
	public const NAME = 'link_rss';
            	public $order = '99';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|max:255';
	public $showOnDetail = true;
	public $title = 'Link Rss';
	public $type = PhpTypes::STRING;
	public $validations = 'required|max:255';

}
