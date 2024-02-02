<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\TrackingReminderNotification;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\UserVariable;
use App\Models\TrackingReminderNotification;
class TrackingReminderNotificationUserVariableButton extends BelongsToRelationshipButton {
	public $foreignKeyName = TrackingReminderNotification::FIELD_USER_VARIABLE_ID;
	public $qualifiedForeignKeyName = TrackingReminderNotification::TABLE.'.'.TrackingReminderNotification::FIELD_USER_VARIABLE_ID;
	public $ownerKeyName = UserVariable::FIELD_ID;
	public $qualifiedOwnerKeyName = UserVariable::TABLE.'.'.UserVariable::FIELD_ID;
	public $childClass = TrackingReminderNotification::class;
	public $parentClass = TrackingReminderNotification::class;
	public $qualifiedParentKeyName = TrackingReminderNotification::TABLE.'.'.TrackingReminderNotification::FIELD_ID;
	public $relatedClass = UserVariable::class;
	public $methodName = 'user_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = UserVariable::COLOR;
	public $fontAwesome = UserVariable::FONT_AWESOME;
	public $id = 'user-variable-button';
	public $image = UserVariable::DEFAULT_IMAGE;
	public $text = 'User Variable';
	public $title = 'User Variable';
	public $tooltip = UserVariable::CLASS_DESCRIPTION;

}
