<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkOwnerProperty extends BaseUserIdProperty
{
	public $canBeChangedToNull = true;
	public $dbInput = 'bigInteger,false,true';
	public $dbType = 'bigint';
	public $default = 'undefined';
	public $description = 'ID of user who created the link.';
	public $example = 7;
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::LINK;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::LINK;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'link_owner';
            	public $order = '99';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|numeric|min:0';
	public $showOnDetail = true;
	public $title = 'Link Owner';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|numeric|min:0';

}
