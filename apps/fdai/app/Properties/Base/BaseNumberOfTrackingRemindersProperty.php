<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\TrackingReminder;
use App\Traits\PropertyTraits\IsNumberOfRelated;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
use App\Properties\BaseProperty;
class BaseNumberOfTrackingRemindersProperty extends BaseProperty{
	use IsNumberOfRelated;
	public $dbInput = 'integer,false';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'number_of_tracking_reminders';
	public $fieldType = self::TYPE_INTEGER;
	public $fontAwesome = FontAwesome::ACTIVITY;
	public $htmlType = 'text';
	public $image = ImageUrls::COMBINE_NOTIFICATIONS;
	public $inForm = true;
	public $inIndex = true;
	public $inView = true;
	public $isFillable = true;
	public $canBeChangedToNull = false;
	public $isOrderable = true;
	public $isSearchable = false;
	public $maximum = 2147483647;
	public $minimum = 0;
	public $name = self::NAME;
	public const NAME = 'number_of_tracking_reminders';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:0|max:2147483647';
	public $title = 'Tracking Reminders';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    protected static function getRelationshipClass(): string{return TrackingReminder::class;}
}
