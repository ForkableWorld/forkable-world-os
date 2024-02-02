<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ConnectorImport;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connector;
use App\Models\ConnectorImport;
class ConnectorImportConnectorButton extends BelongsToRelationshipButton {
	public $foreignKeyName = ConnectorImport::FIELD_CONNECTOR_ID;
	public $qualifiedForeignKeyName = ConnectorImport::TABLE . '.' . ConnectorImport::FIELD_CONNECTOR_ID;
	public $ownerKeyName = Connector::FIELD_ID;
	public $qualifiedOwnerKeyName = Connector::TABLE . '.' . Connector::FIELD_ID;
	public $childClass = ConnectorImport::class;
	public $parentClass = ConnectorImport::class;
	public $qualifiedParentKeyName = ConnectorImport::TABLE . '.' . ConnectorImport::FIELD_ID;
	public $relatedClass = Connector::class;
	public $methodName = 'connector';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Connector::COLOR;
	public $fontAwesome = Connector::FONT_AWESOME;
	public $id = 'connector-button';
	public $image = Connector::DEFAULT_IMAGE;
	public $text = 'Connector';
	public $title = 'Connector';
	public $tooltip = Connector::CLASS_DESCRIPTION;
}
