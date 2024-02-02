<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\TrackingReminder;
class OAClientTrackingRemindersButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
