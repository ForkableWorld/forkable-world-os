<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAAuthorizationCode;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\User;
use App\Models\OAAuthorizationCode;
class OAAuthorizationCodeUserButton extends BelongsToRelationshipButton {
    public $interesting = true;
	public $foreignKeyName = OAAuthorizationCode::FIELD_USER_ID;
	public $qualifiedForeignKeyName = OAAuthorizationCode::TABLE.'.'.OAAuthorizationCode::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE.'.'.User::FIELD_ID;
	public $childClass = OAAuthorizationCode::class;
	public $parentClass = OAAuthorizationCode::class;
	public $qualifiedParentKeyName = OAAuthorizationCode::TABLE.'.'.OAAuthorizationCode::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = 'https://static.quantimo.do/img/robots/quantimodo-robot-puzzled-213-300.png';
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;

}
