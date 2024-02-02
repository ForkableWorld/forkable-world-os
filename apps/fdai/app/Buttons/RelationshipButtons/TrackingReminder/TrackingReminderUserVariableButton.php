<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\TrackingReminder;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\TrackingReminder;
use App\Models\UserVariable;
class TrackingReminderUserVariableButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = TrackingReminder::FIELD_USER_VARIABLE_ID;
	public $qualifiedForeignKeyName = TrackingReminder::TABLE . '.' . TrackingReminder::FIELD_USER_VARIABLE_ID;
	public $ownerKeyName = UserVariable::FIELD_ID;
	public $qualifiedOwnerKeyName = UserVariable::TABLE . '.' . UserVariable::FIELD_ID;
	public $childClass = TrackingReminder::class;
	public $parentClass = TrackingReminder::class;
	public $qualifiedParentKeyName = TrackingReminder::TABLE . '.' . TrackingReminder::FIELD_ID;
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
