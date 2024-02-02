<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseUrlProperty extends BaseProperty
{
    use IsString;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,1083:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'url';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::USER_URL;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::USER_URL;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'url';
	public $order = 99;
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Url';
	public $type = 'string';
	public const WILDCARD_APEX_DOMAIN = '.quantimodo.com';

}
