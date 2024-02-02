<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsString;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseReminderEndTimeProperty extends BaseProperty{
	use IsString;
	public $dbInput = 'time:nullable';
	public $dbType = 'time';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Latest time of day at which reminders should appear';
	public $fieldType = 'time';
	public $fontAwesome = FontAwesome::EARLIEST_REMINDER_TIME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::EARLIEST_REMINDER_TIME;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 8;
	public $name = self::NAME;
	public const NAME = 'reminder_end_time';
	public $phpType = PhpTypes::STRING;
	public $rules = 'nullable|string|max:8';
	public $title = 'Reminder End Time';
	public $type = PhpTypes::STRING;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|string|max:8';

}
