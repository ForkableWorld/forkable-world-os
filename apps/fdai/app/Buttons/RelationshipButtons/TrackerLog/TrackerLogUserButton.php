<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\TrackerLog;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\TrackerLog;
use App\Models\User;
class TrackerLogUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = TrackerLog::FIELD_USER_ID;
	public $qualifiedForeignKeyName = TrackerLog::TABLE . '.' . TrackerLog::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = TrackerLog::class;
	public $parentClass = TrackerLog::class;
	public $qualifiedParentKeyName = TrackerLog::TABLE . '.' . TrackerLog::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = TrackerLog::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
