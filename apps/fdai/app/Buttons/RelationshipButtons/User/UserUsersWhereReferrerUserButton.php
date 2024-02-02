<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\User;
class UserUsersWhereReferrerUserButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'users_where_referrer_user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'users-where-referrer-user-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $text = 'Users Where Referrer User';
	public $title = 'Users Where Referrer User';
	public $tooltip = 'Users where this is the Referrer User';
}
