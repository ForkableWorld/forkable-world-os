<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseWasOkProperty extends BaseProperty
{
    use IsBoolean;
	public $dbInput = self::TYPE_BOOLEAN;
	public $dbType = self::TYPE_BOOLEAN;
	public $default = 'undefined';
	public $description = 'was_ok';
	public $example = 0;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::HANDS_WASH_SOLID;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::BOOKS_96_PNG;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'was_ok';
	public $order = 99;
	public $phpType = self::TYPE_BOOLEAN;
	public $showOnDetail = true;
	public $title = 'Was Ok';
	public $type = self::TYPE_BOOLEAN;

}
