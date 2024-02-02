<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Measurement;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Measurement;
use App\Models\Unit;
class MeasurementUnitButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Measurement::FIELD_UNIT_ID;
	public $qualifiedForeignKeyName = Measurement::TABLE . '.' . Measurement::FIELD_UNIT_ID;
	public $ownerKeyName = Unit::FIELD_ID;
	public $qualifiedOwnerKeyName = Unit::TABLE . '.' . Unit::FIELD_ID;
	public $childClass = Measurement::class;
	public $parentClass = Measurement::class;
	public $qualifiedParentKeyName = Measurement::TABLE . '.' . Measurement::FIELD_ID;
	public $relatedClass = Unit::class;
	public $methodName = 'unit';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Unit::COLOR;
	public $fontAwesome = Unit::FONT_AWESOME;
	public $id = 'unit-button';
	public $image = Unit::DEFAULT_IMAGE;
	public $text = 'Unit';
	public $title = 'Unit';
	public $tooltip = Unit::CLASS_DESCRIPTION;
}
