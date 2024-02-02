<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\MeasurementImport;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\MeasurementImport;
use App\Models\User;
class MeasurementImportUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = MeasurementImport::FIELD_USER_ID;
	public $qualifiedForeignKeyName = MeasurementImport::TABLE . '.' . MeasurementImport::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = MeasurementImport::class;
	public $parentClass = MeasurementImport::class;
	public $qualifiedParentKeyName = MeasurementImport::TABLE . '.' . MeasurementImport::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
