<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Phrase;
use App\Buttons\RelationshipButtons\BelongsToRelationshipButton;
use App\Models\Phrase;
use App\Models\User;
class PhraseUserButton extends BelongsToRelationshipButton {
	public $interesting = true;
	public $foreignKeyName = Phrase::FIELD_USER_ID;
	public $qualifiedForeignKeyName = Phrase::TABLE . '.' . Phrase::FIELD_USER_ID;
	public $ownerKeyName = User::FIELD_ID;
	public $qualifiedOwnerKeyName = User::TABLE . '.' . User::FIELD_ID;
	public $childClass = Phrase::class;
	public $parentClass = Phrase::class;
	public $qualifiedParentKeyName = Phrase::TABLE . '.' . Phrase::FIELD_ID;
	public $relatedClass = User::class;
	public $methodName = 'user';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\BelongsTo';
	public $color = User::COLOR;
	public $fontAwesome = User::FONT_AWESOME;
	public $id = 'user-button';
	public $image = Phrase::DEFAULT_IMAGE;
	public $text = 'User';
	public $title = 'User';
	public $tooltip = User::CLASS_DESCRIPTION;
}
