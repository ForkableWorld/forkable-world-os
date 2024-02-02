<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsTime;
use App\Types\PhpTypes;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseReminderStartTimeProperty extends BaseProperty{
    use IsTime;
	public $dbInput = 'time';
	public $dbType = 'time';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'UTC time of day at which reminder notifications should appear in the case of daily or less frequent reminders.  The earliest UTC time at which notifications should appear in the case of intraday repeating reminders. ';
	public $example = '03:00:00';
	public $fieldType = 'time';
	public $fontAwesome = FontAwesome::EARLIEST_REMINDER_TIME;
	public $htmlInput = 'text';
	public $htmlType = 'text';
	public $image = ImageUrls::EARLIEST_REMINDER_TIME;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = false;
	public $isSearchable = true;
	public $maxLength = 8;
	public $name = self::NAME;
	public const NAME = 'reminder_start_time';
	public $phpType = PhpTypes::STRING;
	public $rules = 'required|string|max:8';
	public $title = 'Time';
	public $type = PhpTypes::STRING;
	public $validations = 'required';
	public const SYNONYMS = ['time', self::NAME];
}
