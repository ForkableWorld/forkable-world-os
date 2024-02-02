<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsBoolean;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseCanBeSummedProperty extends BaseProperty{
	use IsBoolean;
	public $dbInput = self::TYPE_BOOLEAN;
	public $dbType = self::TYPE_BOOLEAN;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Whether measurements can be summed when aggregating.';
	public $example = true;
	public $fieldType = self::TYPE_BOOLEAN;
	public $fontAwesome = FontAwesome::OIL_CAN_SOLID;
	public $htmlInput = 'checkbox,1';
	public $htmlType = 'checkbox';
	public $image = ImageUrls::ACTIVITIES_WATERING_CAN;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $name = self::NAME;
	public const NAME = 'can_be_summed';
	public $phpType = PhpTypes::BOOL;
	public $rules = 'required|boolean';
	public $title = 'Can Be Summed';
	public $type = self::TYPE_BOOLEAN;
	public $validations = 'required';

}
