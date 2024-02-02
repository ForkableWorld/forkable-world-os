<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\TrackingReminderNotification;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\TrackingReminder;
use App\Models\TrackingReminderNotification;
class TrackingReminderNotificationTrackingReminderButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID;
	public $qualifiedForeignKeyName = TrackingReminderNotification::TABLE.'.'.TrackingReminderNotification::FIELD_TRACKING_REMINDER_ID;
	public $ownerKeyName = TrackingReminder::FIELD_ID;
	public $qualifiedOwnerKeyName = TrackingReminder::TABLE.'.'.TrackingReminder::FIELD_ID;
	public $childClass = TrackingReminderNotification::class;
	public $parentClass = TrackingReminderNotification::class;
	public $qualifiedParentKeyName = TrackingReminderNotification::TABLE.'.'.TrackingReminderNotification::FIELD_ID;
	public $relatedClass = TrackingReminder::class;
	public $methodName = 'tracking_reminder';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = TrackingReminder::COLOR;
	public $fontAwesome = TrackingReminder::FONT_AWESOME;
	public $id = 'tracking-reminder-button';
	public $image = TrackingReminder::DEFAULT_IMAGE;
	public $text = 'Tracking Reminder';
	public $title = 'Tracking Reminder';
	public $tooltip = TrackingReminder::CLASS_DESCRIPTION;

}
