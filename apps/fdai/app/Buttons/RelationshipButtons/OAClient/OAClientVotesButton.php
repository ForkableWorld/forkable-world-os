<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\OAClient;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\OAClient;
use App\Models\Vote;
class OAClientVotesButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = OAClient::class;
	public $qualifiedParentKeyName = OAClient::TABLE . '.' . OAClient::FIELD_ID;
	public $relatedClass = Vote::class;
	public $methodName = Vote::TABLE;
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = '#f09402';
	public $fontAwesome = Vote::FONT_AWESOME;
	public $id = 'votes-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $text = 'Votes';
	public $title = 'Votes';
	public $tooltip = Vote::CLASS_DESCRIPTION;
}
