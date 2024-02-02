<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\ConnectorRequest;
use App\Models\User;
class UserConnectorRequestsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
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
