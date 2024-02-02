<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\ConnectorRequest;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\ConnectorRequest;
use App\Models\User;
class ConnectorRequestUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = ConnectorRequest::FIELD_USER_ID;
	public $qualifiedForeignKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = ConnectorRequest::class;
	public $parentClass = ConnectorRequest::class;
	public $qualifiedParentKeyName = ConnectorRequest::TABLE . '.' . ConnectorRequest::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = ConnectorRequest::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
