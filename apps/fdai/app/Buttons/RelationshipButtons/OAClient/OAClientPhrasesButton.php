<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\Phrase;
class OAClientPhrasesButton extends HasManyRelationshipButton {
	public $interesting = true;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
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
