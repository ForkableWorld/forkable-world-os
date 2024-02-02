<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Connection;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Connection;
use App\Models\ConnectorRequest;
class ConnectionConnectorRequestsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = Connection::class;
	public $qualifiedParentKeyName = Connection::TABLE . '.' . Connection::FIELD_ID;
	public $relatedClass = ConnectorRequest::class;
	public $methodName = ConnectorRequest::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = ConnectorRequest::COLOR;
	public $fontAwesome = ConnectorRequest::FONT_AWESOME;
	public $id = 'connector-requests-button';
	public $image = ConnectorRequest::DEFAULT_IMAGE;
	public $text = 'Connector Requests';
	public $title = 'Connector Requests';
	public $tooltip = ConnectorRequest::CLASS_DESCRIPTION;
}
