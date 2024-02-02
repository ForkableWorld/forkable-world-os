<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Measurement;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\ConnectorImport;
use App\Models\Measurement;
class MeasurementConnectorImportButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Measurement::FIELD_CONNECTOR_IMPORT_ID;
	public $qualifiedForeignKeyName = Measurement::TABLE . '.' . Measurement::FIELD_CONNECTOR_IMPORT_ID;
	public $ownerKeyName = ConnectorImport::FIELD_ID;
	public $qualifiedOwnerKeyName = ConnectorImport::TABLE . '.' . ConnectorImport::FIELD_ID;
	public $childClass = Measurement::class;
	public $parentClass = Measurement::class;
	public $qualifiedParentKeyName = Measurement::TABLE . '.' . Measurement::FIELD_ID;
	public $relatedClass = ConnectorImport::class;
	public $methodName = 'connector_import';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = ConnectorImport::COLOR;
	public $fontAwesome = ConnectorImport::FONT_AWESOME;
	public $id = 'connector-import-button';
	public $image = ConnectorImport::DEFAULT_IMAGE;
	public $text = 'Connector Import';
	public $title = 'Connector Import';
	public $tooltip = ConnectorImport::CLASS_DESCRIPTION;
}
