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
class BaseNumberOfDaysProperty extends BaseProperty{
	use IsInt;
    public const MINIMUM_NUMBER_OF_DAYS_IN_COMMON = 4;
    public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of days of data analyzed. ';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::BIRTHDAY;
	public $htmlType = 'text';
	public $image = ImageUrls::BIRTHDAY;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = self::MINIMUM_NUMBER_OF_DAYS_IN_COMMON;
	public $name = self::NAME;
	public const NAME = 'number_of_days';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:0|max:2147483647';
	public $title = 'Days';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';

}
