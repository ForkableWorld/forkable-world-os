<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Credential;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Credential;
use App\Models\User;
class CredentialUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Credential::FIELD_USER_ID;
	public $qualifiedForeignKeyName = Credential::TABLE . '.' . Credential::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Credential::class;
	public $parentClass = Credential::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = Credential::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
