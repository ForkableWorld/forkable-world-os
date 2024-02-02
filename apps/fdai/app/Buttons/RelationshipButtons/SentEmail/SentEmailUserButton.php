<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\SentEmail;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\SentEmail;
use App\Models\User;
class SentEmailUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = SentEmail::FIELD_USER_ID;
	public $qualifiedForeignKeyName = SentEmail::TABLE . '.' . SentEmail::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = SentEmail::class;
	public $parentClass = SentEmail::class;
	public $qualifiedParentKeyName = SentEmail::TABLE . '.' . SentEmail::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = SentEmail::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
