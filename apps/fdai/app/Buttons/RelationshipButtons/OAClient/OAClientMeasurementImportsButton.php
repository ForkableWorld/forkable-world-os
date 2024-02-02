<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\MeasurementImport;
use App\Models\OAClient;
class OAClientMeasurementImportsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
