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
class BaseSortOrderProperty extends BaseProperty
{
    use IsInt;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'The order in which the record should be displayed when returned in a list';
	public $example = 0;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::FIRST_ORDER;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::ANALYTICS_ENGINE_NO_BORDER_PNG;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'sort_order';
            	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Sort Order';
	public $type = self::TYPE_INTEGER;

}
