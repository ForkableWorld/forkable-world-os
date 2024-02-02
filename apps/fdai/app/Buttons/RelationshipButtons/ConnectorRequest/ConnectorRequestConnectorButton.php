<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ConnectorRequest;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Connector;
use App\Models\ConnectorRequest;
class ConnectorRequestConnectorButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = ConnectorRequest::FIELD_CONNECTOR_ID;
	public $qualifiedForeignKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_CONNECTOR_ID;
	public $ownerKeyName = Connector::FIELD_ID;
	public $qualifiedOwnerKeyName = Connector::TABLE . '.' . Connector::FIELD_ID;
	public $childClass = ConnectorRequest::class;
	public $parentClass = ConnectorRequest::class;
	public $qualifiedParentKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_ID;
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
