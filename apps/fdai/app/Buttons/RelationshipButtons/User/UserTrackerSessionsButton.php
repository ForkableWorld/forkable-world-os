<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\TrackerSession;
use App\Models\User;
class UserTrackerSessionsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = TrackerSession::class;
	public $methodName = TrackerSession::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = TrackerSession::COLOR;
	public $fontAwesome = TrackerSession::FONT_AWESOME;
	public $id = 'tracker-sessions-button';
	public $image = TrackerSession::DEFAULT_IMAGE;
	public $text = 'Tracker Sessions';
	public $title = 'Tracker Sessions';
	public $tooltip = TrackerSession::CLASS_DESCRIPTION;
}
