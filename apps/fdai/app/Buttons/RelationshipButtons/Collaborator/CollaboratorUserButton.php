<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Collaborator;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Collaborator;
use App\Models\User;
class CollaboratorUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Collaborator::FIELD_USER_ID;
	public $qualifiedForeignKeyName = Collaborator::TABLE . '.' . Collaborator::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Collaborator::class;
	public $parentClass = Collaborator::class;
	public $qualifiedParentKeyName = Collaborator::TABLE . '.' . Collaborator::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = Collaborator::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
