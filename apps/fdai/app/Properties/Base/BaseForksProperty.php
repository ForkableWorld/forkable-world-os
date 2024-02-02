<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseForksProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $dbInput = 'integer,false';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'Example: 0';
	public $example = 17;
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::ACTIVITIES_FORK;
	public $importance = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = -2147483648;
	public $name = self::NAME;
	public const NAME = 'forks';
	public $order = 99;
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'integer|min:-2147483648|max:2147483647';
	public $showOnDetail = true;
	public $title = 'Forks';
	public $type = self::TYPE_INTEGER;
	public $validations = 'integer|min:-2147483648|max:2147483647';

}
