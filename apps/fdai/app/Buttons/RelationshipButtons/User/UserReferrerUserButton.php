<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\User;
class UserReferrerUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = User::FIELD_REFERRER_USER_ID;
	public $qualifiedForeignKeyName = User::TABLE . '.' . User::FIELD_REFERRER_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = User::class;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'referrer_user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'referrer-user-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $text = 'Referrer User';
	public $title = 'Referrer User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
