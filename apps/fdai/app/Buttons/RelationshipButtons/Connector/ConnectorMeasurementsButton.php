<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connector;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Connector;
use App\Models\Measurement;
class ConnectorMeasurementsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Connector::class;
	public $qualifiedParentKeyName = Connector::TABLE . '.' . Connector::FIELD_ID;
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
