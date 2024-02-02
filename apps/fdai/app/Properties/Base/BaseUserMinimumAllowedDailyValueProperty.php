<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsFloat;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseUserMinimumAllowedDailyValueProperty extends BaseValueProperty{
	use IsFloat;
	public $dbInput = 'float,10,0';
	public $dbType = 'float';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'user_minimum_allowed_daily_value';
	public $fieldType = 'float';
	public $fontAwesome = FontAwesome::OLD_USER;
	public $htmlType = 'text';
	public $image = ImageUrls::OLD_USER;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'user_minimum_allowed_daily_value';
	public $phpType = 'float';
	public $rules = 'nullable|numeric';
	public $title = 'User Minimum Allowed Daily Value';
	public $type = self::TYPE_NUMBER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric';
}
