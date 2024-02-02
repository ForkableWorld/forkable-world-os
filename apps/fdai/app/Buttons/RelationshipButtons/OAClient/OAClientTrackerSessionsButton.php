<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\TrackerSession;
class OAClientTrackerSessionsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
