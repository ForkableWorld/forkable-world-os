<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connection;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Connection;
use App\Models\ConnectorImport;
class ConnectionConnectorImportsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Connection::class;
	public $qualifiedParentKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
	public $relatedClass = ConnectorImport::class;
	public $methodName = ConnectorImport::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = ConnectorImport::COLOR;
	public $fontAwesome = ConnectorImport::FONT_AWESOME;
	public $id = 'connector-imports-button';
	public $image = ConnectorImport::DEFAULT_IMAGE;
	public $text = 'Connector Imports';
	public $title = 'Connector Imports';
	public $tooltip = ConnectorImport::CLASS_DESCRIPTION;
}
