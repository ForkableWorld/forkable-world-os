<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsUrl;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseSourceUrlProperty extends BaseProperty
{
    use IsUrl;
	public $canBeChangedToNull = true;
	public $dbInput = 'string,2083:nullable';
	public $dbType = 'string';
	public $default = 'undefined';
	public $description = 'source_url';
	public $fieldType = 'string';
	public $fontAwesome = FontAwesome::SOURCE;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::SOURCE;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'source_url';
            	public $order = '99';
	public $phpType = 'string';
	public $showOnDetail = true;
	public $title = 'Source Url';
	public $type = 'string';

}
