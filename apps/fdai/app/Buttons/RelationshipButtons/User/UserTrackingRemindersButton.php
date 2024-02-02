<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\TrackingReminder;
use App\Models\User;
class UserTrackingRemindersButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = TrackingReminder::class;
	public $methodName = TrackingReminder::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = TrackingReminder::COLOR;
	public $fontAwesome = TrackingReminder::FONT_AWESOME;
	public $id = 'tracking-reminders-button';
	public $image = TrackingReminder::DEFAULT_IMAGE;
	public $text = 'Tracking Reminders';
	public $title = 'Tracking Reminders';
	public $tooltip = TrackingReminder::CLASS_DESCRIPTION;
}
