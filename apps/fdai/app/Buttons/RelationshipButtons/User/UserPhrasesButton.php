<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\User;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Phrase;
use App\Models\User;
class UserPhrasesButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = User::class;
	public $qualifiedParentKeyName = 'id';
	public $relatedClass = Phrase::class;
	public $methodName = Phrase::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = Phrase::COLOR;
	public $fontAwesome = Phrase::FONT_AWESOME;
	public $id = 'phrases-button';
	public $image = Phrase::DEFAULT_IMAGE;
	public $text = 'Phrases';
	public $title = 'Phrases';
	public $tooltip = Phrase::CLASS_DESCRIPTION;
}
