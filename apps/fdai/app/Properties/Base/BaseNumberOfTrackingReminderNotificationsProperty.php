<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfTrackingReminderNotificationsProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'Number of Tracking Reminder Notifications';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlType = self::TYPE_NUMBER;
	public $image = ImageUrls::COMBINE_NOTIFICATIONS;
	public $isOrderable = true;
	public $isSearchable = false;
	public $name = self::NAME;
	public const NAME = 'number_of_tracking_reminder_notifications';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $title = 'Tracking Reminder Notifications';
	public $type = self::TYPE_INTEGER;

}
