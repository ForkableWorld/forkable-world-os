<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Measurement;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Measurement;
use App\Models\Variable;
class MeasurementVariableButton extends BelongsToRelationshipButton {
	public $foreignKeyName = Measurement::FIELD_VARIABLE_ID;
	public $qualifiedForeignKeyName = Measurement::TABLE . '.' . Measurement::FIELD_VARIABLE_ID;
	public $ownerKeyName = Variable::FIELD_ID;
	public $qualifiedOwnerKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $childClass = Measurement::class;
	public $parentClass = Measurement::class;
	public $qualifiedParentKeyName = Measurement::TABLE . '.' . Measurement::FIELD_ID;
	public $relatedClass = Variable::class;
	public $methodName = 'variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Variable::COLOR;
	public $fontAwesome = Variable::FONT_AWESOME;
	public $id = 'variable-button';
	public $image = Variable::DEFAULT_IMAGE;
	public $text = 'Variable';
	public $title = 'Variable';
	public $tooltip = Variable::CLASS_DESCRIPTION;
}
