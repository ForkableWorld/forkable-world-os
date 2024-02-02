<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\MeasurementImport;
use App\Models\User;
class UserMeasurementImportsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = MeasurementImport::class;
	public $methodName = MeasurementImport::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = MeasurementImport::COLOR;
	public $fontAwesome = MeasurementImport::FONT_AWESOME;
	public $id = 'measurement-imports-button';
	public $image = MeasurementImport::DEFAULT_IMAGE;
	public $text = 'Measurement Imports';
	public $title = 'Measurement Imports';
	public $tooltip = MeasurementImport::CLASS_DESCRIPTION;
}
