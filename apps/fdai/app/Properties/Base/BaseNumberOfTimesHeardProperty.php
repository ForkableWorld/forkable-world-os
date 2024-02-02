<?php
namespace App\Properties\Base;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfTimesHeardProperty extends BaseProperty
{
    use \App\Traits\PropertyTraits\IsInt;
	public $canBeChangedToNull = true;
	public $dbInput = 'integer,false';
	public $dbType = self::TYPE_INTEGER;
	public $default = 'undefined';
	public $description = 'number_of_times_heard';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::CALENDAR_TIMES_SOLID;
	public $htmlInput = self::TYPE_NUMBER;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::QUESTION_MARK;
	public $importance = false;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = -2147483648;
	public $name = self::NAME;
	public const NAME = 'number_of_times_heard';
	public $order = 99;
	public $phpType = 'int';
	public $rules = 'nullable|integer|min:-2147483648|max:2147483647';
	public $showOnDetail = true;
	public $title = 'Times Heard';
	public $type = self::TYPE_INTEGER;
	public $validations = 'nullable|nullable|nullable';

}