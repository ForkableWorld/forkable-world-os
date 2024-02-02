<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connection;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connection;
use App\Models\Connector;
class ConnectionConnectorButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Connection::FIELD_CONNECTOR_ID;
	public $qualifiedForeignKeyName = Connection::TABLE . '.' . Connection::FIELD_CONNECTOR_ID;
	public $ownerKeyName = Connector::FIELD_ID;
	public $qualifiedOwnerKeyName = Connector::TABLE . '.' . Connector::FIELD_ID;
	public $childClass = Connection::class;
	public $parentClass = Connection::class;
	public $qualifiedParentKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
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
