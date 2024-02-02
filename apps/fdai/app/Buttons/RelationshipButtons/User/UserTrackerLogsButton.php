<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\TrackerLog;
use App\Models\User;
class UserTrackerLogsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = TrackerLog::class;
	public $methodName = 'tracker_logs';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = TrackerLog::COLOR;
	public $fontAwesome = TrackerLog::FONT_AWESOME;
	public $id = 'tracker-logs-button';
	public $image = TrackerLog::DEFAULT_IMAGE;
	public $text = 'Tracker Logs';
	public $title = 'Tracker Logs';
	public $tooltip = TrackerLog::CLASS_DESCRIPTION;
}
