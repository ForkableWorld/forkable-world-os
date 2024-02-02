<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Connection;
use App\Models\User;
class UserConnectionsButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = Connection::class;
	public $methodName = Connection::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Connection::COLOR;
	public $fontAwesome = Connection::FONT_AWESOME;
	public $id = 'connections-button';
	public $image = Connection::DEFAULT_IMAGE;
	public $text = 'Connections';
	public $title = 'Connections';
	public $tooltip = Connection::CLASS_DESCRIPTION;
}
