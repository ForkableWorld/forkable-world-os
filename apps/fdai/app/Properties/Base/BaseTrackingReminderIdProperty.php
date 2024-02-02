<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Properties\Base;
use App\Models\TrackingReminder;
use App\Traits\ForeignKeyIdTrait;
use App\UI\ImageUrls;
use App\UI\FontAwesome;
class BaseTrackingReminderIdProperty extends BaseIntegerIdProperty{
	use ForeignKeyIdTrait;
	public $dbInput = 'integer,false,true';
	public $dbType = \Doctrine\DBAL\Types\Types::INTEGER;
	public $default = \OpenApi\Generator::UNDEFINED;
	public $description = 'tracking_reminder_id';
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
	public $name = self::NAME;
	public const NAME = 'tracking_reminder_id';
	public $phpType = \App\Types\PhpTypes::INTEGER;
	public $rules = 'required|integer|min:1|max:2147483647';
	public $title = 'Tracking Reminder';
	public $type = self::TYPE_INTEGER;
	public $validations = 'required';
    /**
     * @return TrackingReminder
     */
    public static function getForeignClass(): string{
        return TrackingReminder::class;
    }
}
