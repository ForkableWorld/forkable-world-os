<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Measurement;
use App\Models\Variable;
class VariableMeasurementsButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
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
