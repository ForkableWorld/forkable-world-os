<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLastRunIdProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false,true';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'last_run_id';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CARD_LAST_FOUR;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CARD_LAST_FOUR;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'last_run_id';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Last Run ID';
	public $type = self::TYPE_INTEGER;

}
