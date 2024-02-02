<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ConnectorImport;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connection;
use App\Models\ConnectorImport;
class ConnectorImportConnectionButton extends BelongsToRelationshipButton {
	public $foreignKeyName = ConnectorImport::FIELD_CONNECTION_ID;
	public $qualifiedForeignKeyName = ConnectorImport::TABLE . '.' . ConnectorImport::FIELD_CONNECTION_ID;
	public $ownerKeyName = Connection::FIELD_ID;
	public $qualifiedOwnerKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
	public $childClass = ConnectorImport::class;
	public $parentClass = ConnectorImport::class;
	public $qualifiedParentKeyName = ConnectorImport::TABLE . '.' . ConnectorImport::FIELD_ID;
	public $relatedClass = Connection::class;
	public $methodName = 'connection';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = Connection::COLOR;
	public $fontAwesome = Connection::FONT_AWESOME;
	public $id = 'connection-button';
	public $image = Connection::DEFAULT_IMAGE;
	public $text = 'Connection';
	public $title = 'Connection';
	public $tooltip = Connection::CLASS_DESCRIPTION;
}
