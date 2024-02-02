<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\MeasurementExport;
use App\Models\User;
class UserMeasurementExportsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = MeasurementExport::class;
	public $methodName = MeasurementExport::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = MeasurementExport::COLOR;
	public $fontAwesome = MeasurementExport::FONT_AWESOME;
	public $id = 'measurement-exports-button';
	public $image = MeasurementExport::DEFAULT_IMAGE;
	public $text = 'Measurement Exports';
	public $title = 'Measurement Exports';
	public $tooltip = MeasurementExport::CLASS_DESCRIPTION;
}
