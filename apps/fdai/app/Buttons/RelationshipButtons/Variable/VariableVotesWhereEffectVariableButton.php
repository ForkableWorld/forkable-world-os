<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\RelationshipButtons\Variable;
use App\Buttons\RelationshipButtons\HasManyRelationshipButton;
use App\Models\Variable;
use App\Models\Vote;
class VariableVotesWhereEffectVariableButton extends HasManyRelationshipButton {
	public $interesting = false;
	public $parentClass = Variable::class;
	public $qualifiedParentKeyName = Variable::TABLE . '.' . Variable::FIELD_ID;
	public $relatedClass = Vote::class;
	public $methodName = 'votes_where_effect_variable';
	public $relationshipType = 'Illuminate\\Database\\Eloquent\\Relations\\HasMany';
	public $color = '#f09402';
	public $fontAwesome = Vote::FONT_AWESOME;
	public $id = 'votes-where-effect-variable-button';
	public $image = 'https://static.quantimo.do/img/Ionicons/png/512/help.png';
	public $text = 'Votes Where Effect Variable';
	public $title = 'Votes Where Effect Variable';
	public $tooltip = 'Votes where this is the Effect Variable';
}
