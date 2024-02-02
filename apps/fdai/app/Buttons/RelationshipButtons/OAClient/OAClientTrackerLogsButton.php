<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\TrackerLog;
class OAClientTrackerLogsButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
