<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connection;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Connection;
use App\Models\Measurement;
class ConnectionMeasurementsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Connection::class;
	public $qualifiedParentKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
	public $relatedClass = Measurement::class;
	public $methodName = Measurement::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Measurement::COLOR;
	public $fontAwesome = Measurement::FONT_AWESOME;
	public $id = 'measurements-button';
	public $image = Measurement::DEFAULT_IMAGE;
	public $text = 'Measurements';
	public $title = 'Measurements';
	public $tooltip = Measurement::CLASS_DESCRIPTION;
}
