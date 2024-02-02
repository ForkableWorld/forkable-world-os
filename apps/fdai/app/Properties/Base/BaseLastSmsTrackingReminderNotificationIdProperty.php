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
class BaseLastSmsTrackingReminderNotificationIdProperty extends BaseProperty{
	use IsInt;
	public $dbInput = 'bigInteger,false,true';
	public $dbType = 'bigint';
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'last_sms_tracking_reminder_notification_id';
	public $fieldType = 'bigInteger';
	public $fontAwesome = FontAwesome::LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID;
	public $htmlType = 'text';
	public $image = ImageUrls::LAST_SMS_TRACKING_REMINDER_NOTIFICATION_ID;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $isOrderable = true;
	public $isSearchable = false;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'last_sms_tracking_reminder_notification_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'nullable|numeric|min:0';
	public $title = 'Last Sms Tracking Reminder Notification';
	public $type = self::TYPE_INTEGER;
	public $canBeChangedToNull = true;
	public $validations = 'nullable|numeric|min:0';

}
