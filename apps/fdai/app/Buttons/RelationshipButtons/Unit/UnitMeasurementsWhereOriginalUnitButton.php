<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Unit;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Measurement;
use App\Models\Unit;
class UnitMeasurementsWhereOriginalUnitButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Unit::class;
	public $qualifiedParentKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $relatedClass = Measurement::class;
	public $methodName = 'measurements_where_original_unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Measurement::COLOR;
	public $fontAwesome = Measurement::FONT_AWESOME;
	public $id = 'measurements-where-original-unit-button';
	public $image = Measurement::DEFAULT_IMAGE;
	public $text = 'Measurements Where Original Unit';
	public $title = 'Measurements Where Original Unit';
	public $tooltip = 'Measurements where this is the Original Unit';
}
