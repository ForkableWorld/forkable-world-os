<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseSuiteIdProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $dbInput = 'integer,false,true';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'suite_id';
	public $example = 6;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CLIENT_ID;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::CLIENT_ID;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'suite_id';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Suite ID';
	public $type = self::TYPE_INTEGER;

}
