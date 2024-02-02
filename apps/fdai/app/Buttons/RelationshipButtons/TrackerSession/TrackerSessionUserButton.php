<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\TrackerSession;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\TrackerSession;
use App\Models\User;
class TrackerSessionUserButton extends BelongsToRelationshipButton {
	public $foreignKeyName = TrackerSession::FIELD_USER_ID;
	public $qualifiedForeignKeyName = TrackerSession::TABLE . '.' . TrackerSession::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = TrackerSession::class;
	public $parentClass = TrackerSession::class;
	public $qualifiedParentKeyName = TrackerSession::TABLE . '.' . TrackerSession::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = TrackerSession::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
