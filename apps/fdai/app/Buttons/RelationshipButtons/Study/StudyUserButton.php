<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Study;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Study;
use App\Models\User;
class StudyUserButton extends BelongsToRelationshipButton {
	public $childClass = Study::class;
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $foreignKeyName = Study::FIELD_USER_ID;
	public $id = 'user-button';
	public $image = Study::DEFAULT_IMAGE;
	public $interesting = false;
	public $methodName = 'user';
	public $ownerKeyName = User::FIELD_ID;
	public $parentClass = Study::class;
	public $qualifiedForeignKeyName = Study::TABLE . '.' . Study::FIELD_USER_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $qualifiedParentKeyName = Study::TABLE . '.' . Study::FIELD_ID;
	public $relatedClass = User::class;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
