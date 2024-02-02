<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ConnectorRequest;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connection;
use App\Models\ConnectorRequest;
class ConnectorRequestConnectionButton extends BelongsToRelationshipButton {
	public $foreignKeyName = ConnectorRequest::FIELD_CONNECTION_ID;
	public $qualifiedForeignKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_CONNECTION_ID;
	public $ownerKeyName = Connection::FIELD_ID;
	public $qualifiedOwnerKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
	public $childClass = ConnectorRequest::class;
	public $parentClass = ConnectorRequest::class;
	public $qualifiedParentKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_ID;
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
