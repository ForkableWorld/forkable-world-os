<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseLinkIdProperty extends BaseIdProperty
{
	public $dbInput = 'bigInteger,true,true';
	public $dbType = 'bigint';
	public $default = 'undefined';
	public $description = 'Unique number assigned to each row of the table.';
	public $example = 1;
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::LINK;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::LINK;
	public $importance = false;
	public $isOrderable = true;
	public $isPrimary = true;
	public $isSearchable = false;
	public $minimum = 1;
	public $name = self::NAME;
	public const NAME = 'link_id';
            	public $order = '99';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|numeric|min:1';
	public $showOnDetail = true;
	public $title = 'Link ID';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required|numeric|min:1';

}
