<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNetworkCountProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $dbInput = 'integer,false';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'Example: 0';
	public $example = 0;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::NETWORK_WIRED_SOLID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::ESSENTIAL_COLLECTION_NETWORK;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = -2147483648;
	public $name = self::NAME;
	public const NAME = 'network_count';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'integer|min:-2147483648|max:2147483647';
	public $showOnDetail = true;
	public $title = 'Network Count';
	public $type = self::TYPE_INTEGER;
	public $validations = 'integer|min:-2147483648|max:2147483647';

}
