<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsInt;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLastMemoryUsageMbProperty extends BaseProperty
{
    use IsInt;
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'last_memory_usage_mb';
	public $example = 2;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::MEMORY_SOLID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CARD_LAST_FOUR;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'last_memory_usage_mb';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Last Memory Usage Mb';
	public $type = self::TYPE_INTEGER;

}
