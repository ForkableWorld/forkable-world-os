<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseLifetimeFailuresProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false,true';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'lifetime_failures';
	public $example = 0;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $isOrderable = false;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'lifetime_failures';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $showOnDetail = true;
	public $title = 'Lifetime Failures';
	public $type = self::TYPE_INTEGER;

}
