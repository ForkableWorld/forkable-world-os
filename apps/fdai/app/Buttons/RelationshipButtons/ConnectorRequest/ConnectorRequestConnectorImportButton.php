<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ConnectorRequest;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\ConnectorImport;
use App\Models\ConnectorRequest;
class ConnectorRequestConnectorImportButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID;
	public $qualifiedForeignKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_CONNECTOR_IMPORT_ID;
	public $ownerKeyName = ConnectorImport::FIELD_ID;
	public $qualifiedOwnerKeyName = ConnectorImport::TABLE . '.' . ConnectorImport::FIELD_ID;
	public $childClass = ConnectorRequest::class;
	public $parentClass = ConnectorRequest::class;
	public $qualifiedParentKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_ID;
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
